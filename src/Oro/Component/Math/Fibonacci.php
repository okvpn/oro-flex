<?php

declare(strict_types=1);

namespace Oro\Component\Math;

class Fibonacci
{
    public static function fibonacciSeq($n): int
    {
        $p2 = (1-sqrt(5))/2;
        $p1 = (1+sqrt(5))/2;

        $a = (pow($p1, $n) - pow($p2, $n))/sqrt(5);
        return (int) round($a);
    }
}
