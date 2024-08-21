<?php

namespace Velocit\Container;

interface ContainerInterface
{
    public function get(string $id);
    public function has(string $id): bool;
}
