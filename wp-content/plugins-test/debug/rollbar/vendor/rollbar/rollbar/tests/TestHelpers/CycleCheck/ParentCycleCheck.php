<?php

namespace Rollbar\TestHelpers\CycleCheck;

/** @internal */
class ParentCycleCheck
{
    public \Rollbar\TestHelpers\CycleCheck\ChildCycleCheck $child;
    public function __construct()
    {
        $this->child = new \Rollbar\TestHelpers\CycleCheck\ChildCycleCheck($this);
    }
}
