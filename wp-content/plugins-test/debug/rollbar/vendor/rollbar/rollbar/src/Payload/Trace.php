<?php

declare (strict_types=1);
namespace Rollbar\Payload;

use Rollbar\UtilitiesTrait;
/** @internal */
class Trace implements \Rollbar\Payload\ContentInterface
{
    use UtilitiesTrait;
    public function __construct(private array $frames, private \Rollbar\Payload\ExceptionInfo $exception)
    {
    }
    #[\RollbarWP\Override]
    public function getKey() : string
    {
        return 'trace';
    }
    public function getFrames() : array
    {
        return $this->frames;
    }
    public function setFrames(array $frames) : self
    {
        $this->frames = $frames;
        return $this;
    }
    public function getException() : \Rollbar\Payload\ExceptionInfo
    {
        return $this->exception;
    }
    public function setException(\Rollbar\Payload\ExceptionInfo $exception) : self
    {
        $this->exception = $exception;
        return $this;
    }
    #[\RollbarWP\Override]
    public function serialize()
    {
        $result = array("frames" => $this->frames, "exception" => $this->exception);
        return $this->utilities()->serializeForRollbar($result);
    }
}
