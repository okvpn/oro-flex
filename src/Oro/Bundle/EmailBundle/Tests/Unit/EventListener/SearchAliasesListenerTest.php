<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\EventListener;

use Oro\Bundle\ActivityBundle\Event\SearchAliasesEvent;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\EventListener\SearchAliasesListener;

class SearchAliasesListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var SearchAliasesListener */
    private $listener;

    protected function setUp(): void
    {
        $this->listener = new SearchAliasesListener();
    }

    public function testAddEmailAliasEventSkipped()
    {
        $expectedAliases = [];
        $targetClasses = [];
        $aliases = [];
        $event = new SearchAliasesEvent($aliases, $targetClasses);
        $this->listener->addEmailAliasEvent($event);
        $this->assertEquals($expectedAliases, $event->getAliases());
    }

    public function testAddEmailAliasEvent()
    {
        $expectedAliases = ['oro_email'];
        $targetClasses = [Email::ENTITY_CLASS];
        $aliases = [];
        $event = new SearchAliasesEvent($aliases, $targetClasses);
        $this->listener->addEmailAliasEvent($event);
        $this->assertEquals($expectedAliases, $event->getAliases());
    }
}
