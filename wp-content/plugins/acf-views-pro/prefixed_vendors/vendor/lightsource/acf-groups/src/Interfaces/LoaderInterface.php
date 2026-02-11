<?php

declare (strict_types=1);
namespace org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces;

use Exception;
interface LoaderInterface
{
    /**
     * @throws Exception
     */
    public function signUpGroup(string $namespace, string $fileNameWithExtension) : void;
    /**
     * @throws Exception
     */
    public function signUpGroups(string $namespace, string $folder, string $phpFilePreg = '/.php$/') : void;
    public function getLoadedGroups() : array;
}
