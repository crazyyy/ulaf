<?php

namespace Rollbar\Payload;

use RollbarWP\Rollbar;
/** @internal */
class ExceptionInfoTest extends Rollbar\BaseRollbarTest
{
    public function testClass() : void
    {
        $class = "HelloWorld";
        $exc = new \Rollbar\Payload\ExceptionInfo($class, "message");
        $this->assertEquals($class, $exc->getClass());
        $this->assertEquals("TestClass", $exc->setClass("TestClass")->getClass());
    }
    public function testMessage() : void
    {
        $message = "A message";
        $exc = new \Rollbar\Payload\ExceptionInfo("C", $message);
        $this->assertEquals($message, $exc->getMessage());
        $this->assertEquals("Another", $exc->setMessage("Another")->getMessage());
    }
    public function testDescription() : void
    {
        $description = "long form";
        $exc = new \Rollbar\Payload\ExceptionInfo("C", "s", $description);
        $this->assertEquals($description, $exc->getDescription());
        $this->assertEquals("longer form", $exc->setDescription("longer form")->getDescription());
        $this->assertNull($exc->setDescription(null)->getDescription());
    }
}
