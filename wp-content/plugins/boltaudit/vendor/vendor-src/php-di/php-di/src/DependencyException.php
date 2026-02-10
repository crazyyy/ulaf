<?php

declare (strict_types=1);
namespace BoltAudit\DI;

use BoltAudit\Psr\Container\ContainerExceptionInterface;
/**
 * Exception for the Container.
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
}
