<?php

namespace Velocit\Container;

use Closure;
use ReflectionClass;

class Container implements ContainerInterface
{
    protected $bindings = [];
    protected $instances = [];

    // Binding a class or value to the container
    public function bind(string $abstract, $concrete = null, bool $shared = false)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    // Bind a singleton to the container
    public function singleton(string $abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    // Create an instance of the bound class
    public function make(string $abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->bindings[$abstract])) {
            return $this->resolve($abstract);
        }

        $concrete = $this->bindings[$abstract]['concrete'];

        if ($concrete === $abstract) {
            $object = $this->resolve($concrete);
        } else {
            $object = $this->resolve($concrete);
        }

        if ($this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    // Resolve the class or value from the container
    protected function resolve($concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        if (is_string($concrete)) {
            $reflection = new ReflectionClass($concrete);

            if (!$reflection->isInstantiable()) {
                throw new ContainerException("Class {$concrete} is not instantiable.");
            }

            $constructor = $reflection->getConstructor();

            if (is_null($constructor)) {
                return new $concrete;
            }

            $dependencies = $constructor->getParameters();
            $instances = $this->resolveDependencies($dependencies);

            return $reflection->newInstanceArgs($instances);
        }

        return $concrete;
    }

    // Resolve class dependencies
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();

            if ($type && !$type->isBuiltin()) {
                $results[] = $this->make($type->getName());
            } else {
                throw new ContainerException("Cannot resolve dependency {$dependency->name}");
            }
        }

        return $results;
    }

    // Check if a binding exists in the container
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    // Get an entry from the container
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException("No entry found for {$id}");
        }

        return $this->make($id);
    }
}
