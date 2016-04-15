<?php

namespace Fluent\Logger;

class FluentLogger
{
    public $prefix;
    public $msg;

    public function post($prefix, $msg)
    {
        $this->prefix = $prefix;
        $this->msg = $msg;
    }
}
