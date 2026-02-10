<?php

declare(strict_types=1);

namespace DebugSuite\Packages\League\Container\Argument;

interface ArgumentInterface
{
    /**
     * @return mixed
     */
    public function getValue();
}
