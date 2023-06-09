<?php

namespace Oro\Bundle\SyncBundle\Authentication\Origin;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Returns application url as origin.
 */
class ApplicationOriginProvider implements OriginProviderInterface
{
    /** @var ConfigManager */
    private $configManager;

    /** @var OriginExtractor */
    private $originExtractor;

    public function __construct(
        ConfigManager $configManager,
        OriginExtractor $originExtractor
    ) {
        $this->configManager = $configManager;
        $this->originExtractor = $originExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrigins(): array
    {
        try {
            $origin = $this->originExtractor->fromUrl($this->configManager->get('oro_ui.application_url'));
        } catch (\Exception $e) {
            return [];
        }

        return $origin === null ? [] : [$origin];
    }
}
