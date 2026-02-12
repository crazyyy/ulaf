<?php

namespace BoltAudit\WpMVC\Contracts;

\defined('ABSPATH') || exit;
interface Provider
{
    public function boot();
}
