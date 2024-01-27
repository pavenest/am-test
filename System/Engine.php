<?php

namespace AMT\System;

use AMT\Container\Container;

/**
 * Usage example -
 *
 * $engine = new Engine();
 * $container = $engine->getContainer();
 * $container->get(Two::class);
 * $container->alias(Six::class, 'seven_1');
 * $obj = $container->get('seven_1');
 * $n7 = $container->register(Ten::class, 'the_ten', ['p1' => 6, 'p2' => 5, 'p3' => 8,]);
 *
 */
class Engine
{
    private Container $container;
    public Response $response;
    private string $path;


    public function __construct(string $pluginFile = '')
    {
        $this->path = $pluginFile;
        $this->container = new Container();
        $this->response = $this->container->get(Response::class);
    }


    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    public static function minPhpVersion(): string
    {
        return '8.0';
    }

    public function pluginUrl(): string
    {
        return trailingslashit(plugin_dir_url($this->path));
    }

    public function pluginDir(): string
    {
        return trailingslashit(plugin_dir_path($this->path));
    }
}
