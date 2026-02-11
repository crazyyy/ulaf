<?php

declare (strict_types=1);
namespace Rollbar\Payload;

use Rollbar\SerializerInterface;
/** @internal */
class TraceChain implements \Rollbar\Payload\ContentInterface
{
    public function __construct(private array $traces)
    {
    }
    #[\RollbarWP\Override]
    public function getKey() : string
    {
        return 'trace_chain';
    }
    public function getTraces() : array
    {
        return $this->traces;
    }
    public function setTraces(array $traces) : self
    {
        $this->traces = $traces;
        return $this;
    }
    #[\RollbarWP\Override]
    public function serialize()
    {
        $mapValue = function ($value) {
            if ($value instanceof \Serializable) {
                \trigger_error("Using the Serializable interface has been deprecated.", \E_USER_DEPRECATED);
                return $value->serialize();
            }
            if ($value instanceof SerializerInterface) {
                return $value->serialize();
            }
            return $value;
        };
        return \array_map($mapValue, $this->traces);
    }
}
