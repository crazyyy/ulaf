<?php

declare(strict_types=1);

namespace DebugSuite\Packages\League\Container\Exception;

use DebugSuite\Packages\Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
