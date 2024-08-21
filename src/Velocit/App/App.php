<?php

namespace Velocit\App;

use Closure;
use Velocit\Container\Container;

class App
{
    const VERSION = '1.0.0';
    protected static $instance;
    protected string $basePath;
    protected Container $container;

    protected function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->container = new Container();
        $this->registerCoreBindings();
    }

    public static function getInstance(string $basePath = ''): static
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($basePath);
        }
        return static::$instance;
    }

    protected function registerCoreBindings(): void
    {
        $this->container->singleton(Container::class, fn() => $this->container);
        $this->container->singleton(App::class, fn() => $this);
    }

    public function handleRequest() {}
}
