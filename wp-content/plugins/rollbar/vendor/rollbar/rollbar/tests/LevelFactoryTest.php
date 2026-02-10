<?php

namespace Rollbar;

use Rollbar\Payload\Level;
/** @internal */
class LevelFactoryTest extends \Rollbar\BaseRollbarTest
{
    /**
     * @dataProvider isValidLevelProvider
     */
    public function testIsValidLevelProvider(string $level, bool $expected) : void
    {
        self::assertSame($expected, \Rollbar\LevelFactory::isValidLevel($level));
    }
    public static function isValidLevelProvider() : array
    {
        $data = self::fromNameProvider();
        foreach ($data as &$testParams) {
            $testParams[] = \true;
        }
        $data[] = ['test-stub', \false];
        return $data;
    }
    public function testFromNameInvalid() : void
    {
        self::assertNull(\Rollbar\LevelFactory::fromName('not a level'));
    }
    /**
     * @dataProvider fromNameProvider
     */
    public function testFromName(string $level) : void
    {
        self::assertInstanceOf(Level::class, \Rollbar\LevelFactory::fromName($level));
    }
    public static function fromNameProvider() : array
    {
        return [[Level::EMERGENCY], [Level::ALERT], [Level::CRITICAL], [Level::ERROR], [Level::WARNING], [Level::NOTICE], [Level::INFO], [Level::DEBUG]];
    }
    /**
     * @dataProvider fromNameOrInstanceProvider
     */
    public function testFromNameOrInstance(Level|string $level) : void
    {
        self::assertInstanceOf(Level::class, \Rollbar\LevelFactory::fromName($level));
    }
    public static function fromNameOrInstanceProvider() : array
    {
        return [[Level::EMERGENCY], [Level::ALERT], [\Rollbar\LevelFactory::fromName(Level::CRITICAL)], [Level::ERROR], [Level::WARNING], [\Rollbar\LevelFactory::fromName(Level::NOTICE)], [\Rollbar\LevelFactory::fromName(Level::INFO)], [Level::DEBUG]];
    }
}
