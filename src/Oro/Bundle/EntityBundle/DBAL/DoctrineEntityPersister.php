<?php

declare(strict_types=1);

namespace Oro\Bundle\EntityBundle\DBAL;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\BasicEntityPersister;
use Doctrine\ORM\Utility\PersisterHelper;

class DoctrineEntityPersister extends BasicEntityPersister
{
    /**
     * {@inheritdoc}
     */
    protected function prepareInsertData($entity)
    {
        $result = [];
        $uow    = $this->em->getUnitOfWork();
        if (($versioned = $this->class->isVersioned) !== false) {
            $versionField = $this->class->versionField;
        }

        foreach ($this->class->reflFields as $field => $property) {
            // Logic is copy-past from method UnitOfWork::computeChangeSet() and self::prepareUpdateData()
            if (isset($versionField) && $versionField == $field) {
                continue;
            }

            if (isset($this->class->embeddedClasses[$field])) {
                continue;
            }

            // skip entity identifier. See UOW L624
            if ($this->class->isIdentifier($field) && $this->class->isIdGeneratorIdentity()) {
                continue;
            }

            $newVal = $property->getValue($entity);
            if ( ! isset($this->class->associationMappings[$field])) {
                $columnName = $this->class->columnNames[$field];
                $this->columnTypes[$columnName] = $this->class->fieldMappings[$field]['type'];
                $result[$this->getOwningTable($field)][$columnName] = $newVal;

                continue;
            }

            $assoc = $this->class->associationMappings[$field];

            // Only owning side of x-1 associations can have a FK column.
            if ( ! $assoc['isOwningSide'] || ! ($assoc['type'] & ClassMetadata::TO_ONE)) {
                continue;
            }

            $newValId = null;
            if ($newVal !== null) {
                $newValId = $uow->getEntityIdentifier($newVal);
            }

            $targetClass = $this->em->getClassMetadata($assoc['targetEntity']);
            $owningTable = $this->getOwningTable($field);

            foreach ($assoc['joinColumns'] as $joinColumn) {
                $sourceColumn = $joinColumn['name'];
                $targetColumn = $joinColumn['referencedColumnName'];
                $quotedColumn = $this->quoteStrategy->getJoinColumnName($joinColumn, $this->class, $this->platform);

                $this->quotedColumns[$sourceColumn]  = $quotedColumn;
                $this->columnTypes[$sourceColumn]    = PersisterHelper::getTypeOfColumn($targetColumn, $targetClass, $this->em);
                $result[$owningTable][$sourceColumn] = $newValId
                    ? $newValId[$targetClass->getFieldForColumn($targetColumn)]
                    : null;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function executeInserts()
    {
        $postInsertIds = parent::executeInserts();

        if ($postInsertIds) {
            // Persister returned post-insert IDs
            foreach ($postInsertIds as $postInsertId) {
                $id      = $postInsertId['generatedId'];
                $entity  = $postInsertId['entity'];
                $idField = $this->class->identifier[0];
                $this->class->reflFields[$idField]->setValue($entity, $id);
            }
        }

        return $postInsertIds;
    }
}
