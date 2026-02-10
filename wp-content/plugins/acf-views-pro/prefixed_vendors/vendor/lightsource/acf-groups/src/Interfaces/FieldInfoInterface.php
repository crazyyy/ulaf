<?php

declare (strict_types=1);
namespace org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces;

interface FieldInfoInterface
{
    public function getName() : string;
    public function getType() : string;
    public function getArguments() : array;
    public function isRepeater() : bool;
    // for extending purposes, so arguments can be added/changed on a fly
    public function setArgument(string $name, $value) : void;
    public function setName(string $name) : void;
}
