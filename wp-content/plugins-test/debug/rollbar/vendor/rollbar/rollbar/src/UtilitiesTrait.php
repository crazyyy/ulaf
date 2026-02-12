<?php

declare (strict_types=1);
namespace Rollbar;

/** @internal */
trait UtilitiesTrait
{
    private function utilities() : \Rollbar\Utilities
    {
        static $utilities = null;
        if (null === $utilities) {
            $utilities = new \Rollbar\Utilities();
        }
        return $utilities;
    }
}
