<?php

declare (strict_types=1);
namespace org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces;

use Exception;
interface CreatorInterface
{
    /**
     * @template T
     *
     * @param class-string<T> $groupClass
     *
     * @return T
     * @throws Exception
     */
    public function create(string $groupClass) : AcfGroupInterface;
}
