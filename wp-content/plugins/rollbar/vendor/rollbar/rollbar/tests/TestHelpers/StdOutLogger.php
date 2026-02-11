<?php

namespace Rollbar\TestHelpers;

use Stringable;
use Rollbar\RollbarLogger;
use RollbarWP\Psr\Log\LoggerTrait;
/** @internal */
class StdOutLogger extends RollbarLogger
{
    public function log($level, $message, array $context = array()) : void
    {
        echo '[' . \get_class($this) . ': ' . $level . '] ' . $message;
    }
}
