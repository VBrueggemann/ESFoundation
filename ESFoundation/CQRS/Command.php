<?php

namespace ESFoundation\CQRS;

use ESFoundation\CQRS\Errors\InvalidCommandPayloadException;
use ESFoundation\Traits\Payloadable;

abstract class Command
{
    use Payloadable;

    /**
     * Command constructor.
     * @param $payload
     */
    public function __construct($payload = null)
    {
        $this->setPayload($payload, InvalidCommandPayloadException::class);
    }
}