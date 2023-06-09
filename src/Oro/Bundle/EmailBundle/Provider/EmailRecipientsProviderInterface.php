<?php

namespace Oro\Bundle\EmailBundle\Provider;

use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Model\Recipient;

/**
 * Interface for services which can provide Recipients by EmailRecipientsProviderArgs.
 */
interface EmailRecipientsProviderInterface
{
    /**
     * @return Recipient[]
     */
    public function getRecipients(EmailRecipientsProviderArgs $args);

    /**
     * @return string
     */
    public function getSection(): string;
}
