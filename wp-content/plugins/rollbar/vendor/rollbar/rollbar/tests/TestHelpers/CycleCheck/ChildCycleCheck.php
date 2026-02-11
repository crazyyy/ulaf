<?php

namespace Rollbar\TestHelpers\CycleCheck;

/** @internal */
class ChildCycleCheck
{
    public $parent;
    public function __construct($parent)
    {
        $this->parent = $parent;
    }
}
