<?php

namespace Oro\Bundle\LoggerBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LoggerBundle\DependencyInjection\Configuration;

/**
 * Provides email addresses of recipients for error log email notification.
 */
class ErrorLogNotificationRecipientsProvider
{
    private ConfigManager $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return string[]
     */
    public function getRecipientsEmailAddresses(): array
    {
        $recipients = (string) $this->configManager->get(
            Configuration::getFullConfigKey(Configuration::EMAIL_NOTIFICATION_RECIPIENTS)
        );

        return array_filter(array_map('trim', explode(';', $recipients)));
    }
}
