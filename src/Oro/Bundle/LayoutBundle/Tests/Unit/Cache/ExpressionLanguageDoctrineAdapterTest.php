<?php

namespace Oro\Bundle\LayoutBundle\Tests\Unit\Cache;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\LayoutBundle\Cache\ExpressionLanguageDoctrineAdapter;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Cache\CacheItem;

class ExpressionLanguageDoctrineAdapterTest extends \PHPUnit\Framework\TestCase
{
    private CacheProvider|\PHPUnit\Framework\MockObject\MockObject $provider;

    private ExpressionLanguageDoctrineAdapter $adapter;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(CacheProvider::class);

        $this->adapter = new ExpressionLanguageDoctrineAdapter($this->provider, 'test');
    }

    public function testGetItem(): void
    {
        $key = 'test_item';
        $expectedId = 'orv1thlk24gwoo0k8o0cs8go382qua26l8owcssk04gokso48oooscs';
        $data = 'data';

        $this->provider->expects(self::once())
            ->method('fetchMultiple')
            ->with([$expectedId])
            ->willReturn([$key => $data]);

        $result = $this->adapter->getItem($key);

        self::assertEquals($key, $result->getKey());
        self::assertEquals($data, $result->get());
    }

    public function testGetItems(): void
    {
        $key = 'test_item';
        $expectedId = 'orv1thlk24gwoo0k8o0cs8go382qua26l8owcssk04gokso48oooscs';
        $data = 'data';

        $this->provider->expects(self::once())
            ->method('fetchMultiple')
            ->with([$expectedId])
            ->willReturn([$key => $data]);

        $result = $this->adapter->getItems([$key]);

        $item = $result->current();
        self::assertEquals($key, $item->getKey());
        self::assertEquals($data, $item->get());
    }

    public function testHasItem(): void
    {
        $key = 'test_item';
        $expectedId = 'orv1thlk24gwoo0k8o0cs8go382qua26l8owcssk04gokso48oooscs';

        $this->provider->expects(self::once())
            ->method('contains')
            ->with($expectedId)
            ->willReturn(true);

        self::assertTrue($this->adapter->hasItem($key));
    }

    public function testDelete(): void
    {
        $key = 'test_item';
        $expectedId = 'orv1thlk24gwoo0k8o0cs8go382qua26l8owcssk04gokso48oooscs';

        $this->provider->expects(self::once())
            ->method('delete')
            ->with($expectedId)
            ->willReturn(true);

        self::assertTrue($this->adapter->deleteItem($key));
    }

    public function testSave(): void
    {
        $data = 'data';
        $key = 'test_item';
        $expectedId = 'orv1thlk24gwoo0k8o0cs8go382qua26l8owcssk04gokso48oooscs';

        $item = new CacheItem();
        ReflectionUtil::setPropertyValue($item, 'key', $key);
        ReflectionUtil::setPropertyValue($item, 'value', $data);

        $this->provider->expects(self::once())
            ->method('saveMultiple')
            ->with([$expectedId => $data], 0)
            ->willReturn(true);

        self::assertTrue($this->adapter->save($item));
    }

    public function testClearWhenNamespace(): void
    {
        $this->provider->expects(self::once())
            ->method('getNamespace')
            ->willReturn('sample_namespace');

        $this->provider->expects(self::once())
            ->method('deleteAll')
            ->willReturn(true);

        self::assertTrue($this->adapter->clear('dummy_namespace'));
    }

    public function testClearWhenNoNamespace(): void
    {
        $this->provider->expects(self::once())
            ->method('getNamespace')
            ->willReturn('');

        $this->provider->expects(self::once())
            ->method('flushAll')
            ->willReturn(true);

        self::assertTrue($this->adapter->clear('dummy_namespace'));
    }
}
