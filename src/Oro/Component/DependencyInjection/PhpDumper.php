<?php

namespace Oro\Component\DependencyInjection;

use Symfony\Component\DependencyInjection\Dumper\PhpDumper as SymfonyPhpDumper;

/**
 * Dump private-service-ids.php to allow use private services from UI custom process
 * and more memory optimaze MigrationContainer
 */
class PhpDumper extends SymfonyPhpDumper
{
    private static $privateAliasToServiceMap = [];

    public static function setPrivateAliasMap(array $privateAlias): void
    {
        static::$privateAliasToServiceMap = $privateAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $options = [])
    {
        $code = parent::dump($options);

        return array_merge(['private-service-ids.php' => $this->dumpServiceIdToPrivateMethodNameMap()], $code);
    }

    private function dumpServiceIdToPrivateMethodNameMap(): string
    {
        $reflect = new \ReflectionClass(SymfonyPhpDumper::class);
        $prop = $reflect->getProperty('serviceIdToMethodNameMap');
        $prop->setAccessible(true);
        $value = $prop->getValue($this);
        foreach (self::$privateAliasToServiceMap as $aliasId => $serviceId) {
            if (!isset($value[$aliasId])) {
                $value[$aliasId] = "__service." . $serviceId;
            }
        }

        ksort($value);
        $result = var_export($value, true);

        $fileTemplate = <<<EOF
<?php

return $result;
EOF;
        return $fileTemplate;
    }
}
