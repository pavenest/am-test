<?php

namespace AMT\Container;

use Closure;

interface ContainerContract extends ContainerInterface
{

    /**
     * Alias a type to a different name.
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     *
     */
    public function alias($abstract, $alias);


    /**
     * Register a binding with the container.
     *
     * Usage :
     *
     *  $container->bind(\AMT\Test\Ten::class, null,['p1'=> 'd', 'p2'=> 4, 5, 'p3'=> 88, 99]);
     *  $container->get(\AMT\Test\Ten::class);
     *  $container->bind('hamari', function ($tumari) use($kumari) {return 'Hahaha-'. $tumari.'--'.$kumari;}, [66]);
     *  $container->get('hamari');
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, array $arguments = [], bool $shared = false);

}
