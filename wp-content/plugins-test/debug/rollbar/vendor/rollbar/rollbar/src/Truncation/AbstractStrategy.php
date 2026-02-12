<?php

declare (strict_types=1);
namespace Rollbar\Truncation;

use Rollbar\Payload\EncodedPayload;
/**
 * The base for all Rollbar truncation classes.
 *
 * @since 1.1.0
 * @internal
 */
abstract class AbstractStrategy implements \Rollbar\Truncation\StrategyInterface
{
    public function __construct(protected \Rollbar\Truncation\Truncation $truncation)
    {
    }
    #[\RollbarWP\Override]
    public function execute(EncodedPayload $payload) : EncodedPayload
    {
        return $payload;
    }
    #[\RollbarWP\Override]
    public function applies(EncodedPayload $payload) : bool
    {
        return \true;
    }
}
