<?php

namespace Rollbar;

use Rollbar\Payload\Body;
use Rollbar\Payload\Data;
use Rollbar\Payload\Message;
use Stringable;
use Throwable;
/** @internal */
class FakeDataBuilder implements \Rollbar\DataBuilderInterface
{
    public static array $args = array();
    public static array $logged = array();
    public function __construct($arr)
    {
        self::$args[] = $arr;
    }
    public function makeData(string $level, Throwable|string|Stringable $toLog, array $context) : Data
    {
        self::$logged[] = array($level, $toLog, $context);
        return new Data('test', new Body(new Message('test')));
    }
    public function setCustom(array $config) : void
    {
    }
    public function addCustom(string $key, mixed $data) : void
    {
    }
    public function removeCustom(string $key) : void
    {
    }
    public function getCustom() : ?array
    {
        return null;
    }
    public function generateErrorWrapper(int $errno, string $errstr, ?string $errfile, ?int $errline) : \Rollbar\ErrorWrapper
    {
        return new \Rollbar\ErrorWrapper($errno, $errstr, $errfile, $errline, [], new \Rollbar\Utilities());
    }
}
