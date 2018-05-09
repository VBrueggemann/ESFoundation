<?php

namespace ESFoundation\CQRS\Errors;

use Illuminate\Support\MessageBag;

class InvalidCommandPayloadException extends \Exception
{
 public function __construct(MessageBag $errors)
 {
     parent::__construct($errors, 0, null);
 }
}