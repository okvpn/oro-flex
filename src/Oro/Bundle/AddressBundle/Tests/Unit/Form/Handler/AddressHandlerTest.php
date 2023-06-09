<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AddressHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var Request */
    private $request;

    /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject */
    private $om;

    /** @var Address|\PHPUnit\Framework\MockObject\MockObject */
    private $address;

    /** @var AddressHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->request = new Request();
        $this->om = $this->createMock(ObjectManager::class);
        $this->address = $this->createMock(Address::class);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->handler = new AddressHandler($this->form, $requestStack, $this->om);
    }

    public function testGoodRequest()
    {
        $this->form->expects($this->once())
            ->method('setData');

        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('submit');
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn('true');

        $this->om->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($this->address));
        $this->om->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->address));
    }

    public function testBadRequest()
    {
        $this->form->expects($this->once())
            ->method('setData');

        $this->request->setMethod('GET');

        $this->form->expects($this->never())
            ->method('submit');
        $this->form->expects($this->never())
            ->method('isValid')
            ->willReturn(true);

        $this->om->expects($this->never())
            ->method('persist');
        $this->om->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->address));
    }

    public function testNotValidForm()
    {
        $this->form->expects($this->once())
            ->method('setData');

        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('submit');
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->om->expects($this->never())
            ->method('persist');
        $this->om->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->address));
    }
}
