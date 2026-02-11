<?php

declare(strict_types=1);

namespace DebugSuite\Packages\League\Container\Exception;

use DebugSuite\Packages\Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
