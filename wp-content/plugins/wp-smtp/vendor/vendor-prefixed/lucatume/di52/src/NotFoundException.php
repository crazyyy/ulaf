<?php
/**
 * An exception used to signal no binding was found for container ID.
 *
 * @package lucatume\DI52
 *
 * @license GPL-3.0
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Mail\lucatume\DI52;

use SolidWP\Mail\Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 *
 * @package lucatume\DI52
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
