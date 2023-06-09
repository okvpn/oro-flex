<?php

namespace Oro\Bundle\TranslationBundle\EventListener;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\ImportExportBundle\Event\AfterJobExecutionEvent;
use Oro\Bundle\TranslationBundle\Translation\DynamicTranslationMetadataCache;

/**
 * Updates the timestamp of the last update of database translations.
 */
class TranslationListener
{
    /** @var DynamicTranslationMetadataCache */
    protected $metadataCache;

    /** @var string */
    protected $jobName;

    /**
     * @param DynamicTranslationMetadataCache $metadataCache
     * @param string $jobName
     */
    public function __construct(DynamicTranslationMetadataCache $metadataCache, $jobName)
    {
        $this->metadataCache = $metadataCache;
        $this->jobName = $jobName;
    }

    public function onAfterImportTranslations(AfterJobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();
        $jobResult = $event->getJobResult();

        if ($jobResult->isSuccessful() && $this->isApplicable($jobExecution)) {
            $languageCode = $jobExecution->getExecutionContext()->get('language_code');

            if (!empty($languageCode)) {
                $this->metadataCache->updateTimestamp($languageCode);
            }
        }
    }

    /**
     * @param JobExecution $jobExecution
     * @return bool
     */
    protected function isApplicable(JobExecution $jobExecution)
    {
        return $this->jobName === $jobExecution->getLabel();
    }
}
