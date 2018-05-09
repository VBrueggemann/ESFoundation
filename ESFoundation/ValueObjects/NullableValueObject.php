<?php

namespace ESFoundation\ValueObjects;

use Illuminate\Support\Facades\Validator;


/**
 * Class ValueObject
 *
 * @package App\ValueObjects
 */
abstract class NullableValueObject extends ValueObject
{
    public static function validate($input): bool
    {
        return Validator::make(['value' => $input], [
            'value' => 'nullable|' . static::rules()
        ])->passes();
    }
}