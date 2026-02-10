<?php

declare(strict_types=1);

namespace DebugSuite\Packages\League\Container\Argument;

use DebugSuite\Packages\League\Container\ContainerAwareInterface;
use ReflectionFunctionAbstract;

interface ArgumentResolverInterface extends ContainerAwareInterface
{
    public function resolveArguments(array $arguments): array;
    public function reflectArguments(ReflectionFunctionAbstract $method, array $args = []): array;
}
