<?php

namespace Rollbar;

use RollbarWP\PHPUnit\Framework\TestCase;
/** @internal */
abstract class BaseRollbarTest extends TestCase
{
    const DEFAULT_ACCESS_TOKEN = 'ad865e76e7fb496fab096ac07b1dbabb';
    public function tearDown() : void
    {
        \Rollbar\Rollbar::destroy();
        parent::tearDown();
    }
    public function getTestAccessToken()
    {
        return $_ENV['ROLLBAR_TEST_TOKEN'] ?? static::DEFAULT_ACCESS_TOKEN;
    }
}
