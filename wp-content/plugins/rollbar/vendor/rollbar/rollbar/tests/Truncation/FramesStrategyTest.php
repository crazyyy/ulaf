<?php

namespace Rollbar\Truncation;

use Rollbar\Payload\EncodedPayload;
use Rollbar\Config;
use Rollbar\BaseRollbarTest;
/** @internal */
class FramesStrategyTest extends BaseRollbarTest
{
    /**
     * @dataProvider executeProvider
     */
    public function testExecute(array $data, array $expected) : void
    {
        $config = new Config(array('access_token' => $this->getTestAccessToken()));
        $truncation = new \Rollbar\Truncation\Truncation($config);
        $strategy = new \Rollbar\Truncation\FramesStrategy($truncation);
        $data = new EncodedPayload($data);
        $data->encode();
        $result = $strategy->execute($data);
        $this->assertEquals($expected, $result->data());
    }
    /**
     * Also used by {@see TruncationTest::truncateProvider()}.
     *
     * @return array
     */
    public static function executeProvider() : array
    {
        $data = array('nothing to truncate using trace key' => array(array('data' => array('body' => array('trace' => array('frames' => \range(1, 6))))), array('data' => array('body' => array('trace' => array('frames' => \range(1, 6)))))), 'nothing to truncate using trace_chain key' => array(array('data' => array('body' => array('trace_chain' => array(array('frames' => \range(1, 6)))))), array('data' => array('body' => array('trace_chain' => array(array('frames' => \range(1, 6))))))), 'truncate middle using trace key' => array(array('data' => array('body' => array('trace' => array('frames' => \range(1, \Rollbar\Truncation\FramesStrategy::FRAMES_OPTIMIZATION_RANGE * 2 + 1))))), array('data' => array('body' => array('trace' => array('frames' => \array_merge(\range(1, \Rollbar\Truncation\FramesStrategy::FRAMES_OPTIMIZATION_RANGE), \range(\Rollbar\Truncation\FramesStrategy::FRAMES_OPTIMIZATION_RANGE + 2, \Rollbar\Truncation\FramesStrategy::FRAMES_OPTIMIZATION_RANGE * 2 + 1))))))), 'truncate middle using trace_chain key' => array(array('data' => array('body' => array('trace_chain' => array(array('frames' => \range(1, \Rollbar\Truncation\FramesStrategy::FRAMES_OPTIMIZATION_RANGE * 2 + 1)))))), array('data' => array('body' => array('trace_chain' => array(array('frames' => \array_merge(\range(1, \Rollbar\Truncation\FramesStrategy::FRAMES_OPTIMIZATION_RANGE), \range(\Rollbar\Truncation\FramesStrategy::FRAMES_OPTIMIZATION_RANGE + 2, \Rollbar\Truncation\FramesStrategy::FRAMES_OPTIMIZATION_RANGE * 2 + 1)))))))));
        return $data;
    }
}
