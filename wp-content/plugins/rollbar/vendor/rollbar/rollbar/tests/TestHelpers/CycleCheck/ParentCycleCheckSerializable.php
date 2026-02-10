<?php

namespace Rollbar\TestHelpers\CycleCheck;

use Rollbar\SerializerInterface;
/** @internal */
class ParentCycleCheckSerializable implements SerializerInterface
{
    public \Rollbar\TestHelpers\CycleCheck\ChildCycleCheck $child;
    public function __construct()
    {
        $this->child = new \Rollbar\TestHelpers\CycleCheck\ChildCycleCheck($this);
    }
    public function serialize() : array
    {
        return array("child" => \Rollbar\Utilities::serializeForRollbarInternal($this->child));
    }
}
