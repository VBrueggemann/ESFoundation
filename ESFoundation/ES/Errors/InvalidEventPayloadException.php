<?php

namespace ESFoundation\ES\Errors;

use Illuminate\Support\MessageBag;

class InvalidEventPayloadException extends \Exception
{
 public function __construct(MessageBag $errors)
 {
     parent::__construct($errors, 0, null);
 }
}