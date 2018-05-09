<?php

namespace ESFoundation\ValueObjects;

use ESFoundation\ValueObjects\Errors\ValueObjectGuardException;
use Illuminate\Support\Facades\Validator;


/**
 * Class ValueObject
 *
 * @package App\ValueObjects
 */
abstract class ValueObject
{
    protected $value;

    public function __construct($value) {

        if(!$this->guard($value)) {
            throw new ValueObjectGuardException($value . ' violates the guarding rules for: ' . get_class($this));
        }

        $this->value = $value;
    }

    public function __get($var) {

        if(! isset($this->$var)) {
            return;
        }

        return $this->$var;
    }

    protected function guard($value) : bool
    {
        return static::validate($value);
    }

    public static function validate($input): bool
    {
        return Validator::make(['value' => $input], [
            'value' => 'required|' . static::rules()
        ])->passes();
    }

    public function __toString()
    {
        return $this->value;
    }

    abstract public static function rules(): string;
}