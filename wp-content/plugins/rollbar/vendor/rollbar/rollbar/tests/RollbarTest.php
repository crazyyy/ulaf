<?php

namespace Rollbar;

use Rollbar\Payload\Payload;
use Rollbar\Payload\Level;
use Rollbar\Payload\TelemetryEvent;
use Rollbar\Telemetry\EventLevel;
use Rollbar\Telemetry\EventType;
use Rollbar\Telemetry\Telemeter;
use Rollbar\TestHelpers\ArrayLogger;
/**
 * Usage of static method Rollbar::logger() is intended here.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @internal
 */
class RollbarTest extends \Rollbar\BaseRollbarTest
{
    private static $simpleConfig = array();
    public function setUp() : void
    {
        self::$simpleConfig['access_token'] = $this->getTestAccessToken();
        self::$simpleConfig['environment'] = 'test';
    }
    public static function setUpBeforeClass() : void
    {
        \Rollbar\Rollbar::destroy();
    }
    public function testInitWithConfig() : void
    {
        \Rollbar\Rollbar::init(self::$simpleConfig);
        $this->assertInstanceOf(\Rollbar\RollbarLogger::class, \Rollbar\Rollbar::logger());
        $this->assertEquals(new \Rollbar\Config(self::$simpleConfig), \Rollbar\Rollbar::logger()->getConfig());
    }
    public function testInitWithLogger() : void
    {
        $logger = $this->getMockBuilder(\Rollbar\RollbarLogger::class)->disableOriginalConstructor()->getMock();
        \Rollbar\Rollbar::init($logger);
        $this->assertSame($logger, \Rollbar\Rollbar::logger());
    }
    public function testInitConfigureLogger() : void
    {
        $logger = $this->getMockBuilder(\Rollbar\RollbarLogger::class)->disableOriginalConstructor()->getMock();
        $logger->expects($this->once())->method('configure')->with(self::$simpleConfig);
        \Rollbar\Rollbar::init($logger);
        \Rollbar\Rollbar::init(self::$simpleConfig);
    }
    public function testInitReplaceLogger() : void
    {
        \Rollbar\Rollbar::init(self::$simpleConfig);
        $this->assertInstanceOf(\Rollbar\RollbarLogger::class, \Rollbar\Rollbar::logger());
        $logger = $this->getMockBuilder(\Rollbar\RollbarLogger::class)->disableOriginalConstructor()->getMock();
        \Rollbar\Rollbar::init($logger);
        $this->assertSame($logger, \Rollbar\Rollbar::logger());
    }
    public function testInitTelemeter() : void
    {
        // Default telemeter is enabled
        \Rollbar\Rollbar::init(self::$simpleConfig);
        $this->assertInstanceOf(Telemeter::class, \Rollbar\Rollbar::getTelemeter());
        // Ensure telemeter is disabled when config is set to false
        \Rollbar\Rollbar::init(\array_merge(['telemetry' => \false], self::$simpleConfig));
        $this->assertNull(\Rollbar\Rollbar::getTelemeter());
    }
    public function testLogException() : void
    {
        \Rollbar\Rollbar::init(self::$simpleConfig);
        try {
            throw new \Exception('test exception');
        } catch (\Exception $e) {
            \Rollbar\Rollbar::log(Level::ERROR, $e);
        }
        $this->assertTrue(\true);
    }
    public function testLogMessage() : void
    {
        \Rollbar\Rollbar::init(self::$simpleConfig);
        \Rollbar\Rollbar::log(Level::INFO, 'testing info level');
        $this->assertTrue(\true);
    }
    public function testLogExtraData() : void
    {
        \Rollbar\Rollbar::init(self::$simpleConfig);
        $logger = \Rollbar\Rollbar::logger();
        $reflection = new \ReflectionClass(\get_class($logger));
        $method = $reflection->getMethod('getPayload');
        $payload = $method->invokeArgs($logger, array(self::$simpleConfig['access_token'], Level::INFO, 'testing extra data', array("some_key" => "some value")));
        $extra = $payload->getData()->getBody()->getExtra();
        $this->assertEquals("some value", $extra['some_key']);
    }
    public function testDebug() : void
    {
        $this->shortcutMethodTestHelper(Level::DEBUG);
    }
    public function testInfo() : void
    {
        $this->shortcutMethodTestHelper(Level::INFO);
    }
    public function testNotice() : void
    {
        $this->shortcutMethodTestHelper(Level::NOTICE);
    }
    public function testWarning() : void
    {
        $this->shortcutMethodTestHelper(Level::WARNING);
    }
    public function testError() : void
    {
        $this->shortcutMethodTestHelper(Level::ERROR);
    }
    public function testCritical() : void
    {
        $this->shortcutMethodTestHelper(Level::CRITICAL);
    }
    public function testAlert() : void
    {
        $this->shortcutMethodTestHelper(Level::ALERT);
    }
    public function testEmergency() : void
    {
        $this->shortcutMethodTestHelper(Level::EMERGENCY);
    }
    public function testCaptureTelemetryEvent() : void
    {
        \Rollbar\Rollbar::init(self::$simpleConfig);
        $event = \Rollbar\Rollbar::captureTelemetryEvent(type: EventType::Log, level: EventLevel::Info, metadata: ['message' => 'test message']);
        self::assertInstanceOf(TelemetryEvent::class, $event);
        self::assertEquals(EventType::Log, $event->type);
        self::assertEquals('test message', $event->body->message);
        self::assertEquals(EventLevel::Info, $event->level);
    }
    protected function shortcutMethodTestHelper($level) : void
    {
        $message = "shortcutMethodTestHelper: {$level}";
        $verbose = new ArrayLogger();
        \Rollbar\Rollbar::init(array('verbose_logger' => $verbose));
        $result = \Rollbar\Rollbar::$level($message);
        $this->assertEquals(1, $verbose->count(Level::INFO, "Attempting to log: [{$level}] " . $message));
        $this->assertEquals(1, $verbose->count(Level::INFO, 'Occurrence successfully logged'));
        $expected = \Rollbar\Rollbar::report($level, $message);
        $this->assertEquals(2, $verbose->count(Level::INFO, "Attempting to log: [{$level}] " . $message));
        $this->assertEquals(2, $verbose->count(Level::INFO, 'Occurrence successfully logged'));
    }
    public function testBackwardsFlush() : void
    {
        \Rollbar\Rollbar::init(self::$simpleConfig);
        \Rollbar\Rollbar::flush();
        $this->assertTrue(\true);
    }
    public function testConfigure() : void
    {
        $expected = 'expectedEnv';
        \Rollbar\Rollbar::init(self::$simpleConfig);
        // functionality under test
        \Rollbar\Rollbar::configure(array('environment' => $expected));
        // assertion
        $logger = \Rollbar\Rollbar::logger();
        $dataBuilder = $logger->getDataBuilder();
        $data = $dataBuilder->makeData(Level::ERROR, "testing", array());
        $payload = new Payload($data, self::$simpleConfig['access_token']);
        $this->assertEquals($expected, $payload->getData()->getEnvironment());
    }
    public function testEnable() : void
    {
        \Rollbar\Rollbar::init(self::$simpleConfig);
        $this->assertTrue(\Rollbar\Rollbar::enabled());
        \Rollbar\Rollbar::disable();
        $this->assertTrue(\Rollbar\Rollbar::disabled());
        \Rollbar\Rollbar::enable();
        $this->assertTrue(\Rollbar\Rollbar::enabled());
        \Rollbar\Rollbar::init(\array_merge(self::$simpleConfig, array('enabled' => \false)));
        $this->assertTrue(\Rollbar\Rollbar::disabled());
        \Rollbar\Rollbar::configure(array('enabled' => \true));
        $this->assertTrue(\Rollbar\Rollbar::enabled());
        \Rollbar\Rollbar::configure(array('enabled' => \false));
        $this->assertTrue(\Rollbar\Rollbar::disabled());
    }
    public function testLogUncaughtUnsetLogger() : void
    {
        $sut = new \Rollbar\Rollbar();
        $result = $sut::logUncaught('level', new \Exception());
        $expected = new \Rollbar\Response(0, "Rollbar Not Initialized");
        $this->assertEquals($expected, $result);
    }
    public function testLogUncaught() : void
    {
        $test = $this;
        \Rollbar\Rollbar::init(\array_merge(self::$simpleConfig, ['check_ignore' => function ($isUncaught) use($test) {
            $test::assertTrue($isUncaught);
        }]));
        $toLog = new \Exception();
        $result = \Rollbar\Rollbar::logUncaught(Level::ERROR, $toLog);
        $this->assertEquals(200, $result->getStatus());
    }
}
