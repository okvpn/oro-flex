<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Model;

use Oro\Bundle\LocaleBundle\Model\FallbackType;

class FallbackTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructAndGetType()
    {
        $type = FallbackType::SYSTEM;
        $fallbackType = new FallbackType($type);

        self::assertEquals($type, $fallbackType->getType());
    }
}
