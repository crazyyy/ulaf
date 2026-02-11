<?php

declare (strict_types=1);
namespace Rollbar\Payload;

use Rollbar\Defaults;
use Rollbar\SerializerInterface;
use Rollbar\UtilitiesTrait;
/** @internal */
class Data implements SerializerInterface
{
    use UtilitiesTrait;
    private \Rollbar\Payload\Level|string|null $level = null;
    private ?int $timestamp = null;
    private ?string $codeVersion = null;
    private ?string $platform = null;
    private ?string $language = null;
    private ?string $framework = null;
    private ?string $context = null;
    private ?\Rollbar\Payload\Request $request = null;
    private ?\Rollbar\Payload\Person $person = null;
    private ?\Rollbar\Payload\Server $server = null;
    private ?array $custom = null;
    private ?string $fingerprint = null;
    private ?string $title = null;
    private ?string $uuid = null;
    private ?\Rollbar\Payload\Notifier $notifier = null;
    public function __construct(private string $environment, private \Rollbar\Payload\Body $body)
    {
    }
    public function getEnvironment() : string
    {
        return $this->environment;
    }
    public function setEnvironment(string $environment) : self
    {
        $this->environment = $environment;
        return $this;
    }
    public function getBody() : \Rollbar\Payload\Body
    {
        return $this->body;
    }
    public function setBody(\Rollbar\Payload\Body $body) : self
    {
        $this->body = $body;
        return $this;
    }
    public function getLevel() : \Rollbar\Payload\Level|string|null
    {
        return $this->level;
    }
    public function setLevel(\Rollbar\Payload\Level|string|null $level) : self
    {
        $this->level = $level;
        return $this;
    }
    public function getTimestamp() : ?int
    {
        return $this->timestamp;
    }
    public function setTimestamp(?int $timestamp) : self
    {
        $this->timestamp = $timestamp;
        return $this;
    }
    public function getCodeVersion() : ?string
    {
        return $this->codeVersion;
    }
    public function setCodeVersion(?string $codeVersion) : self
    {
        $this->codeVersion = $codeVersion;
        return $this;
    }
    public function getPlatform() : ?string
    {
        return $this->platform;
    }
    public function setPlatform(?string $platform) : self
    {
        $this->platform = $platform;
        return $this;
    }
    public function getLanguage() : ?string
    {
        return $this->language;
    }
    public function setLanguage(?string $language) : self
    {
        $this->language = $language;
        return $this;
    }
    public function getFramework() : ?string
    {
        return $this->framework;
    }
    public function setFramework(?string $framework) : self
    {
        $this->framework = $framework;
        return $this;
    }
    public function getContext() : ?string
    {
        return $this->context;
    }
    public function setContext(?string $context) : self
    {
        $this->context = $context;
        return $this;
    }
    public function getRequest() : ?\Rollbar\Payload\Request
    {
        return $this->request;
    }
    public function setRequest(?\Rollbar\Payload\Request $request = null) : self
    {
        $this->request = $request;
        return $this;
    }
    public function getPerson() : ?\Rollbar\Payload\Person
    {
        return $this->person;
    }
    public function setPerson(?\Rollbar\Payload\Person $person = null) : self
    {
        $this->person = $person;
        return $this;
    }
    public function getServer() : ?\Rollbar\Payload\Server
    {
        return $this->server;
    }
    public function setServer(?\Rollbar\Payload\Server $server = null) : self
    {
        $this->server = $server;
        return $this;
    }
    public function getCustom() : ?array
    {
        return $this->custom;
    }
    public function setCustom(?array $custom = null) : self
    {
        $this->custom = $custom;
        return $this;
    }
    public function getFingerprint() : ?string
    {
        return $this->fingerprint;
    }
    public function setFingerprint(?string $fingerprint) : self
    {
        $this->fingerprint = $fingerprint;
        return $this;
    }
    public function getTitle() : ?string
    {
        return $this->title;
    }
    public function setTitle(?string $title) : self
    {
        $this->title = $title;
        return $this;
    }
    public function getUuid() : ?string
    {
        return $this->uuid;
    }
    public function setUuid(?string $uuid) : self
    {
        $this->uuid = $uuid;
        return $this;
    }
    public function getNotifier() : ?\Rollbar\Payload\Notifier
    {
        return $this->notifier;
    }
    public function setNotifier(\Rollbar\Payload\Notifier $notifier) : self
    {
        $this->notifier = $notifier;
        return $this;
    }
    #[\RollbarWP\Override]
    public function serialize()
    {
        $result = array("environment" => $this->environment, "body" => $this->body, "level" => $this->level, "timestamp" => $this->timestamp, "code_version" => $this->codeVersion, "platform" => $this->platform, "language" => $this->language, "framework" => $this->framework, "context" => $this->context, "request" => $this->request, "person" => $this->person, "server" => $this->server, "custom" => $this->custom, "fingerprint" => $this->fingerprint, "title" => $this->title, "uuid" => $this->uuid, "notifier" => $this->notifier);
        return $this->utilities()->serializeForRollbarInternal($result);
    }
}
