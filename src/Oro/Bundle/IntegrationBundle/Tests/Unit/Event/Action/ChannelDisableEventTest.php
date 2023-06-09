<?php

namespace Oro\Bundle\IntegrationBundle\Tests\Unit\Event\Action;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Event\Action\ChannelDisableEvent;

class ChannelDisableEventTest extends \PHPUnit\Framework\TestCase
{
    public function testGetName()
    {
        $event = new ChannelDisableEvent(new Channel());

        self::assertSame('oro_integration.channel_disable', $event->getName());
    }

    public function testSettersGetters()
    {
        $channel = new Channel();
        $event = new ChannelDisableEvent($channel);

        $event->addError('error1');

        self::assertSame($channel, $event->getChannel());
        self::assertEquals(new ArrayCollection(['error1']), $event->getErrors());
    }
}
