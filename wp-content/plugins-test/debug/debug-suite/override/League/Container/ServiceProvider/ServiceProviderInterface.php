<?php

declare(strict_types=1);

namespace DebugSuite\Packages\League\Container\ServiceProvider;

use DebugSuite\Packages\League\Container\ContainerAwareInterface;

interface ServiceProviderInterface extends ContainerAwareInterface
{
    public function getIdentifier(): string;
    public function provides(string $id): bool;
    public function register(): void;
    public function setIdentifier(string $id): ServiceProviderInterface;
}
