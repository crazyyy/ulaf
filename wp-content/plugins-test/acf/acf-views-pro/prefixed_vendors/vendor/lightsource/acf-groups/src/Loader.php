<?php

declare (strict_types=1);
namespace org\wplake\acf_views\vendors\LightSource\AcfGroups;

use Exception;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\AcfGroupInterface;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\CreatorInterface;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\LoaderInterface;
use ReflectionClass;
class Loader implements LoaderInterface
{
    private array $loadedGroups;
    private float $loadedTimeInSeconds;
    private int $numberOfGroups;
    public function __construct()
    {
        $this->loadedGroups = [];
        $this->loadedTimeInSeconds = 0;
        $this->numberOfGroups = 0;
    }
    /**
     * @param string|class-string<AcfGroupInterface|mixed> $phpClass
     *
     * @throws Exception
     */
    protected function getAcfGroupInfo(string $phpClass) : ?array
    {
        if (!\class_exists($phpClass, \true) || !\in_array(AcfGroupInterface::class, \class_implements($phpClass), \true)) {
            // without any error, because php files can contain other things
            return null;
        }
        try {
            $reflectionClass = new ReflectionClass($phpClass);
        } catch (Exception $ex) {
            // without any error, because php files can contain other things
            return null;
        }
        // ignore abstract or not local groups
        if ($reflectionClass->isAbstract()) {
            return null;
        }
        $isLocalGroup = \call_user_func([$phpClass, 'isLocalGroup']);
        if ($isLocalGroup) {
            $groupInfo = \call_user_func([$phpClass, 'getGroupInfo']);
            $this->loadedGroups[] = $phpClass;
            return $groupInfo;
        }
        return null;
    }
    /**
     * @throws Exception
     */
    protected function loadFiles(string $namespace, array $phpFileNames) : array
    {
        $acfGroupsInfo = [];
        foreach ($phpFileNames as $phpFileName) {
            $phpClass = \implode('\\', [$namespace, \str_replace('.php', '', $phpFileName)]);
            $acfGroupInfo = $this->getAcfGroupInfo($phpClass);
            if (!$acfGroupInfo) {
                continue;
            }
            $acfGroupsInfo[] = $acfGroupInfo;
        }
        return $acfGroupsInfo;
    }
    /**
     * @throws Exception
     */
    protected function loadDirectory(string $directory, string $namespace, string $phpFilePreg = '/.php$/') : array
    {
        $acfGroupsInfo = [];
        // exclude ., ..
        $fs = \array_diff(\scandir($directory), ['.', '..']);
        $phpFileNames = \array_filter($fs, function ($f) use($phpFilePreg) {
            return 1 === \preg_match($phpFilePreg, $f);
        });
        $phpFileNames = \array_values($phpFileNames);
        $subDirectoryNames = \array_filter($fs, function ($f) {
            return \false === \strpos($f, '.');
        });
        $subDirectoryNames = \array_values($subDirectoryNames);
        foreach ($subDirectoryNames as $subDirectoryName) {
            $subDirectory = \implode(\DIRECTORY_SEPARATOR, [$directory, $subDirectoryName]);
            $subNamespace = \implode('\\', [$namespace, $subDirectoryName]);
            $acfGroupsInfo = \array_merge($acfGroupsInfo, $this->loadDirectory($subDirectory, $subNamespace, $phpFilePreg));
        }
        return \array_merge($acfGroupsInfo, $this->loadFiles($namespace, $phpFileNames));
    }
    protected function signUpGroupsInAcf(array $acfGroupsInfo) : void
    {
        $signUpFunction = function () use($acfGroupsInfo) {
            if (!\function_exists('acf_add_local_field_group')) {
                return;
            }
            foreach ($acfGroupsInfo as $acfGroupInfo) {
                \acf_add_local_field_group($acfGroupInfo);
            }
        };
        // acf_add_local_field_group() method should be called in the acf init action
        if (\function_exists('add_action')) {
            \add_action('acf/init', $signUpFunction);
        } else {
            // just for tests
            $signUpFunction();
        }
    }
    public function getLoadedGroups() : array
    {
        return $this->loadedGroups;
    }
    /**
     * @throws Exception
     */
    public function signUpGroup(string $namespace, string $fileNameWithExtension) : void
    {
        $acfGroupsInfo = $this->loadFiles($namespace, [$fileNameWithExtension]);
        $this->signUpGroupsInAcf($acfGroupsInfo);
    }
    /**
     * @throws Exception
     */
    public function signUpGroups(string $namespace, string $folder, string $phpFilePreg = '/.php$/') : void
    {
        $loadStartTime = \microtime(\true);
        $acfGroupsInfo = $this->loadDirectory($folder, $namespace, $phpFilePreg);
        $this->loadedTimeInSeconds += \microtime(\true) - $loadStartTime;
        $this->numberOfGroups += \count($acfGroupsInfo);
        $this->signUpGroupsInAcf($acfGroupsInfo);
    }
    public function getLoadedTimeInSeconds() : float
    {
        return $this->loadedTimeInSeconds;
    }
    public function getNumberOfGroups() : int
    {
        return $this->numberOfGroups;
    }
}
