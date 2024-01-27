<?php

namespace AMT\Traits;

/**
 * For the usage of singleton design pattern
 *
 * @author pavenest
 * @since 1.0.0
 *
 */
trait Singleton
{

    private static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
