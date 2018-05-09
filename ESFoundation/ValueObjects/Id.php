<?php

namespace ESFoundation\ValueObjects;

use Ramsey\Uuid\Uuid;

abstract class Id extends ValueObject
{
    public function guard($value): bool
    {
        return Uuid::isValid($value);
    }

    public static function rules(): string
    {
        return 'required';
    }
}
