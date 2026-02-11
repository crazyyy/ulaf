<?php

declare (strict_types=1);
namespace Rollbar\Payload;

use Rollbar\DataBuilder;
use Rollbar\Config;
use Rollbar\SerializerInterface;
use Rollbar\UtilitiesTrait;
/** @internal */
class Payload implements SerializerInterface
{
    use UtilitiesTrait;
    public function __construct(private \Rollbar\Payload\Data $data, private string $accessToken)
    {
    }
    /**
     * @return Data
     */
    public function getData() : \Rollbar\Payload\Data
    {
        return $this->data;
    }
    public function setData(\Rollbar\Payload\Data $data) : self
    {
        $this->data = $data;
        return $this;
    }
    public function getAccessToken() : string
    {
        return $this->accessToken;
    }
    public function setAccessToken(string $accessToken) : self
    {
        $this->accessToken = $accessToken;
        return $this;
    }
    #[\RollbarWP\Override]
    public function serialize($maxDepth = -1) : array
    {
        $objectHashes = array();
        $result = array("data" => $this->data, "access_token" => $this->accessToken);
        return $this->utilities()->serializeForRollbar($result, null, $objectHashes, $maxDepth);
    }
}
