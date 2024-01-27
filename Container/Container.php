<?php

namespace AMT\Container;


/**
 *
 * Need implementing : $this->instance('config', new Config($data));
 *
 * $container = $engine->getContainer();
 * $container->get(Two::class);
 * $container->alias(Six::class, 'seven_1');
 * $obj = $container->get('seven_1');
 * $n7 = $container->register(Ten::class, 'the_ten', ['p1' => 6, 'p2' => 5, 'p3' => 8,]);
 *
 *
 */
class Container implements ContainerContract
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * An array of the types that have been resolved.
     *
     * @var bool[]
     */
    protected $resolved = [];
    protected $resolvedEntries = [];

    /**
     * The container's bindings.
     *
     * @var array[]
     */
    protected $bindings = [];


    /**
     * The container's shared instances.
     *
     * @var object[]
     */
    protected $instances = [];


    /**
     * The registered type aliases.
     *
     * @var string[]
     */
    protected $aliases = [];


    /**
     *
     */
    public function __construct()
    {

        $this->resolvedEntries = [
            self::class               => $this,
            FactoryInterface::class   => $this,
            ContainerInterface::class => $this,
        ];
    }


    /**
     * Determine if the given abstract type has been bound.
     *
     * {@inheritdoc}
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->resolvedEntries[$id]) || $this->isAlias($id);
    }


    /**
     *
     * For the time being get will always resolve in factory
     *
     *
     * @param string $id
     * @return \Closure|mixed|object
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function get(string $id)
    {
        try {

            return $this->resolve($id);

        } catch (BindingResolutionException $ex) {

            throw $ex;

        } catch (DependencyException $ex) {

            throw $ex;

        } catch (\Exception $e) {

            if ($this->has($id)) {
                throw $e;
            }

            throw new NotFoundException(
                sprintf("Resource '%s' has not been registered with the container.", $id),
                is_int($e->getCode()) ? $e->getCode() : 0,
                $e
            );
        }
    }


    public function getSingleton(string $id)
    {
        try {

            return $this->resolve($id, [], true);

        } catch (DependencyException $ex) {

            throw $ex;

        } catch (\Exception $e) {

            if ($this->has($id)) {
                throw $e;
            }

            throw new NotFoundException(
                sprintf("Resource '%s' has not been registered with the container.", $id),
                is_int($e->getCode()) ? $e->getCode() : 0,
                $e
            );
        }
    }


    /**
     * This is an alias of get method with additional alias and parameter option
     *
     * @param string $abstract
     * @param string $alias
     * @param $param
     * @return mixed|object|null
     * @throws BindingResolutionException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function register(string $abstract, string $alias = '', $param = [])
    {
        try {

            if (!empty($alias)) {
                $this->alias($abstract, $alias);
            }

            return $this->resolve($abstract, $param);

        } catch (BindingResolutionException $ex) {

            throw $ex;

        } catch (DependencyException $ex) {

            throw $ex;

        } catch (\Exception $e) {

            if ($this->has($id)) {
                throw $e;
            }

            throw new NotFoundException(
                sprintf("Resource '%s' has not been registered with the container.", $id),
                is_int($e->getCode()) ? $e->getCode() : 0,
                $e
            );
        }
    }


    /**
     * Get the alias for an abstract if available.
     *
     * @param $abstract
     * @return string
     */
    protected function getAlias($abstract)
    {
        return $this->aliases[$abstract] ?? $abstract;
    }


    /**
     *
     * $app->alias(\FluentCart\Api\StoreSettings::class, 'store_settings');
     * @param $abstract
     * @param $alias
     * @return void
     */
    public function alias($abstract, $alias)
    {
        $this->aliases[$alias] = $abstract;
    }


    /**
     * Resolve the given type from the container.
     *
     * @param $abstract
     * @param array $parameters
     * @return mixed|object|null
     * @throws DependencyException
     * @throws \ReflectionException
     */
    protected function resolve($abstract, array $parameters = [], $isSingleton = false): mixed
    {

        $abstract = $this->getAlias($abstract);


        /*
         *  If an instance of the type is currently being managed as a singleton we'll
         *  just return an existing instance instead of instantiating new instances
         *
         */
        if ($this->isShared($abstract)) {

            return $this->instances[$abstract];
        }

        /**
         * instead of building every time when get is called we will return the resolved instance
         */
        if ($this->isResolved($abstract)) {

            return $this->resolvedEntries[$abstract];
        }


        if (is_string($abstract) && class_exists($abstract)) {

            $object = $this->buildClass($abstract, $parameters);

            if ($isSingleton === true) {
                $this->instances[$abstract] = $object;
                unset($this->resolvedEntries[$abstract]);
            }

            return $object;

        } elseif ($abstract instanceof \Closure) {

            throw new DependencyException("Closure is not allowed in resolve, please bind the closure first.");

        } elseif (function_exists($abstract)) {

            $value = $this->buildFunction($abstract, $parameters);

            /**
             * This eventually caches the function cal!
             */
            if ($isSingleton === true) {
                $this->instances[$abstract] = $value;
                unset($this->resolvedEntries[$abstract]);
            }

            return $value;
        }

        // we will do method binging later and other primitives too.

        throw new DependencyException(
            sprintf("Resource '%s' is not known to the container.", $abstract),
        );
    }


    /**
     *
     * @param $abstract
     * @param $parameters
     * @return mixed|object|null
     * @throws BindingResolutionException
     * @throws DependencyException
     * @throws \ReflectionException
     */
    protected function buildClass($abstract, $parameters = [])
    {

        $reflection = new \ReflectionClass($abstract);


        if ($reflection->isAbstract() || !$reflection->isInstantiable()) {

            throw new DependencyException(
                sprintf("Resource '%s' is not instantiable.", $abstract),
            );
        }


        $constructor = $reflection->getConstructor();

        /**
         * Do you know -
         * $var === null and $var === NULL is not same !!! wt.....
         */
        if (is_null($constructor)) {

            $this->resolvedEntries[$abstract] = new $abstract;

            return $this->resolvedEntries[$abstract];
        }

        $deps = $constructor->getParameters();

        if (empty($deps)) {

            $this->resolvedEntries[$abstract] = new $abstract;

            return $this->resolvedEntries[$abstract];
        }


        $requiredParam = [];
        $required = 0;

        foreach ($deps as $k => $p) {

            if ($p->isOptional()) {
                continue;
            }

            $required++;

            $requiredParam[$k] = $p;
        }

        if ($required < 1) {

            $this->resolvedEntries[$abstract] = new $abstract;

            return $this->resolvedEntries[$abstract];
        }


        $instances = $this->resolveDependencies($deps, $parameters);


        $this->resolvedEntries[$abstract] = $reflection->newInstanceArgs($instances);

        return $this->resolvedEntries[$abstract];

        /**
         * todo - need to check circular dependency....
         */
    }


    /**
     * This will actually cache the function call for the time being
     *
     * @param $abstract
     * @param array $params
     * @return mixed
     * @throws DependencyException
     * @throws \ReflectionException
     */
    protected function buildFunction($abstract, array $params = []): mixed
    {

        $reflection = new \ReflectionFunction($abstract);

        $parameters = $reflection->getParameters();

        if (empty($parameters)) {

            $this->resolvedEntries[$abstract] = $abstract();

            return $this->resolvedEntries[$abstract];
        }

        foreach ($parameters as $k => $p) {

            if (!$p->isOptional()) {

                throw new DependencyException(
                    sprintf("Function '%s' parameter '%s' is required, we are not allowing it for the time being.", $abstract, $p->getName()),
                );
            }
        }

        $this->resolvedEntries[$abstract] = $abstract();

        return $this->resolvedEntries[$abstract];
    }


    /**
     *
     * @param $parameters
     * @param array $givenDefault
     * @return array|void
     * @throws BindingResolutionException
     */
    protected function resolveDependencies($parameters, array $givenDefault = [])
    {
        $dependencies = [];

        $types = ['bool', 'int', 'float', 'string', 'array', 'resource'];

        foreach ($parameters as $parameter) {

            $dependency = $parameter->getType();

            /**
             * For un-hinted parameter
             */
            if (is_null($dependency)) {

                /**
                 * https://stackoverflow.com/a/3210982/18530504
                 */
                if (array_key_exists($parameter->getName(), $givenDefault)) {

                    $dependencies[] = $givenDefault[$parameter->getName()];

                    continue;
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();

                    continue;
                }

                throw new BindingResolutionException(
                    sprintf(
                        'Un-hinted primitive dependency with required param, can not resolve - "%s" in "%s". ',
                        $parameter->getName(),
                        $parameter->getDeclaringClass()->getName()
                    )
                );

            }

            $dependency = $parameter->getType()->getName();

            if ($dependency && in_array($dependency, $types)) {

                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();

                    continue;
                }

                /**
                 * https://stackoverflow.com/a/3210982/18530504
                 */
                if (array_key_exists($parameter->getName(), $givenDefault)) {

                    $dependencies[] = $givenDefault[$parameter->getName()];

                    continue;
                }


                throw new BindingResolutionException(
                    sprintf(
                        'Unresolvable primitive dependency "%s" in "%s"  ...["%s"].',
                        $parameter->getName(),
                        $dependency,
                        $parameter->getDeclaringClass()->getName()
                    )
                );
            }

            if (array_key_exists($parameter->getName(), $givenDefault)) {

                if (is_object($givenDefault[$parameter->getName])) {

                    $dependencies[] = $givenDefault[$parameter->getName];

                    continue;
                }

                throw new BindingResolutionException(
                    sprintf(
                        'Currently building object from argument is not allowed. Thrown for "%s" in "%s"  ...["%s"].',
                        $parameter->getName(),
                        $dependency,
                        $givenDefault[$parameter->getName]
                    )
                );
            }


            /**
             * This must be a class ....
             *
             */
            $dependencies[] = $this->resolveClass($parameter, $givenDefault);

        }

        return $dependencies;
    }


    /**
     * @param \ReflectionParameter $parameter
     * @param  $arguments
     * @return mixed|object|null
     * @throws DependencyException
     * @throws \ReflectionException
     */
    protected function resolveClass(\ReflectionParameter $parameter, $arguments = [])
    {

        return $this->resolve($parameter->getType()->getName(), $arguments);


        /*
         *

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        */
    }


    /**
     * Register a binding with the container.
     *
     * @param $abstract
     * @param \Closure|string|null $concrete
     * @param $arguments
     * @param bool $shared
     * @return $this|bool|float|int|mixed|string|void
     * @throws BindingResolutionException
     * @throws DependencyException
     * @throws \ReflectionException
     */
    public function bind($abstract, $concrete = null, $arguments = [], bool $shared = false)
    {
        $abstract = $this->getAlias($abstract);

        if ($this->isBound($abstract)) {

            throw new BindingResolutionException(
                sprintf("Resource '%s' is already bound, rebound is not allowed for the time being.", $abstract),
            );
        }

        if (is_null($concrete)) {

            return $this->resolve($abstract, $arguments, $shared === true);
        }

        if ($concrete instanceof \Closure) {

            try {
                $val = call_user_func_array($concrete, $arguments);

            } catch (\ArgumentCountError $error) {

                throw new DependencyException(
                    'Closure argument count mismatch - ' . $error->getMessage(),
                    $error->getCode() ?? 0,
                    $error
                );

            } catch (\TypeError $err) {

                throw new DependencyException(
                    'Closure dependency error - ' . $err->getMessage(),
                    $err->getCode() ?? 0,
                    $err
                );
            } catch (\Exception $e) {

                throw new DependencyException(
                    'Closure call failed due to error - ' . $e->getMessage(),
                    $e->getCode() ?? 0,
                    $e
                );
            }

            $this->resolvedEntries[$abstract] = $val;

            return $this->resolvedEntries[$abstract];

        } elseif (is_object($concrete)) {

            $this->instances[$abstract] = $concrete;

            return $this->instances[$abstract];

        } elseif (is_string($concrete) || is_numeric($concrete) || is_bool($concrete)) {

            $this->resolvedEntries[$abstract] = $concrete;

            return $this->resolvedEntries[$abstract];
        }

        throw new BindingResolutionException(
            sprintf("Resource '%s' can not be bound due to unknown type of concrete.", $abstract),
        );
    }


    /**
     * Determine if a given type is shared/singleton.
     *
     * @param string $abstract
     * @return bool
     */
    protected function isShared($abstract)
    {
        return isset($this->instances[$abstract]);
    }

    /**
     * Determine if the abstract is already resolved in the container
     *
     * @param $abstract
     * @return bool
     */
    protected function isResolved($abstract): bool
    {
        return isset($this->resolvedEntries[$abstract]);
    }


    /**
     *
     * @param $abstract
     * @return bool
     */
    protected function isBound($abstract): bool
    {
        return isset($this->resolvedEntries[$abstract]) || isset($this->instances[$abstract]);
    }


    /**
     * Determine if a given string is an alias.
     *
     * @param string $name
     * @return bool
     */
    public function isAlias($name): bool
    {
        return isset($this->aliases[$name]);
    }


    /**
     * Register an existing instance as shared in the container.
     * todo - need work no usage now
     * $this->instance('config', new Config($data));
     *
     * @param string $abstract
     * @param mixed $instance
     * @return mixed
     */
    public function instance($abstract, $instance)
    {

        //$isBound = $this->bound($abstract);

        unset($this->aliases[$abstract]);

        // We'll check to determine if this type has been bound before, and if it has
        // we will fire the rebound callbacks registered with the container and it
        // can be updated with consuming classes that have gotten resolved here.
        $this->instances[$abstract] = $instance;

        //if ($isBound) {
        //$this->rebound($abstract);
        //}

        return $instance;
    }


    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function flush()
    {
        $this->aliases = [];
        $this->resolvedEntries = [];
        $this->instances = [];
    }

}
