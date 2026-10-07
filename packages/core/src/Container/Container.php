<?php
namespace Ferrox\Core\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;

/**
 * PSR-11 compliant Dependency Injection Container for Ferrox PHP.
 * Supports automatic resolving of dependencies (Autowiring) via Reflection
 * and enforces singleton states for memory-resident environments like RoadRunner.
 */
class Container implements ContainerInterface
{
    private array $instances = [];
    private array $bindings = [];

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @return mixed Entry.
     * @throws RuntimeException Error while retrieving the entry.
     */
    public function get(string $id)
    {
        if ($this->hasInstance($id)) {
            return $this->instances[$id];
        }

        if (!$this->has($id)) {
            // Autowiring fallback
            if (class_exists($id)) {
                return $this->resolve($id);
            }
            throw new RuntimeException("No binding found for {$id}");
        }

        $concrete = $this->bindings[$id];

        if ($concrete instanceof \Closure) {
            $instance = $concrete($this);
        } else {
            $instance = $this->resolve($concrete);
        }

        // Maintain singleton state
        $this->instances[$id] = $instance;
        return $instance;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     *
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    private function hasInstance(string $id): bool
    {
        return isset($this->instances[$id]);
    }

    /**
     * Registers a binding with the container.
     *
     * @param string $id The abstract type or interface.
     * @param mixed $concrete The concrete implementation or a closure.
     */
    public function bind(string $id, $concrete = null): void
    {
        if (is_null($concrete)) {
            $concrete = $id;
        }
        $this->bindings[$id] = $concrete;
    }

    /**
     * Registers an existing instance as shared in the container.
     *
     * @param string $id
     * @param object $instance
     */
    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }

    /**
     * Automatically resolves a class and its dependencies using Reflection.
     *
     * @param string $concrete
     * @return object
     * @throws RuntimeException
     */
    private function resolve(string $concrete)
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new RuntimeException("Target class [$concrete] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencyType = $parameter->getType();
            if ($dependencyType && !$dependencyType->isBuiltin()) {
                $dependencies[] = $this->get($dependencyType->getName());
            } else {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new RuntimeException("Cannot resolve non-class dependency {$parameter->getName()}");
                }
            }
        }

        return $dependencies;
    }
}
