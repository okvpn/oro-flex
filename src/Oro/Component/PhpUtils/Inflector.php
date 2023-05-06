<?php

namespace Oro\Component\PhpUtils;

use Doctrine\Inflector\Rules\English\InflectorFactory;

/**
 * Static Inflector
 */
class Inflector
{
    private static $inflector;

    public static function tableize(string $word): string
    {
        return static::__execute(__FUNCTION__, func_get_args());
    }

    public static function classify(string $word): string
    {
        return static::__execute(__FUNCTION__, func_get_args());
    }

    public static function camelize(string $word): string
    {
        return static::__execute(__FUNCTION__, func_get_args());
    }

    public static function capitalize(string $string, string $delimiters = " \n\t\r\0\x0B-"): string
    {
        return static::__execute(__FUNCTION__, func_get_args());
    }

    public static function seemsUtf8(string $string): bool
    {
        return static::__execute(__FUNCTION__, func_get_args());
    }

    public static function unaccent(string $string): string
    {
        return static::__execute(__FUNCTION__, func_get_args());
    }

    public static function urlize(string $string): string
    {
        return static::__execute(__FUNCTION__, func_get_args());
    }

    public static function singularize(string $word): string
    {
        return static::__execute(__FUNCTION__, func_get_args());
    }

    public static function pluralize(string $word): string
    {
        return static::__execute(__FUNCTION__, func_get_args());
    }

    private static function __execute(string $method, array $args)
    {
        if (null === static::$inflector) {
            static::$inflector = (new InflectorFactory())->build();
        }

        return static::$inflector->{$method}(...$args);
    }
}
