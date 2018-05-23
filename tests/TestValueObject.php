<?php

namespace ESFoundation\ES\ValueObjects;

use ESFoundation\ValueObjects\ValueObject;
use Illuminate\Validation\Rule;

class TestValueObject extends ValueObject
{
    public static function rules(): string
    {
        return Rule::in(['one', 'two']);
    }
}