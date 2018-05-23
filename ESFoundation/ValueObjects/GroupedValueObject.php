<?php

namespace ESFoundation\ValueObjects;

use ESFoundation\ES\ValueObjects\AggregateRootId;
use Illuminate\Support\Collection;

abstract class GroupedValueObject
{
    protected $values;

    public function __construct(Collection $values = null)
    {
        $this->values = $this->valueObjects()->map(function ($valueObjectClass, $key) use ($values) {
            if ($values && $values->get($key)) {
                return new $valueObjectClass($values->get($key));
            }
            return;
        })->filter();
    }

    abstract public static function valueObjects() : Collection;

    public function __get($key)
    {
        $keys = str_contains($key, '.') ? explode('.', $key) : $key;

        if (is_array($keys)) {
            $lowerLevelValueObject = $this->values->get(array_shift($keys));
            if ($lowerLevelValueObject) {
                return $lowerLevelValueObject->__get(implode('.', $keys));
            }
            return null;
        }

        if ($value = $this->values->get($key)) {
            return $value->value;
        }

        return $value;
    }

    public function put($key, $value)
    {
        $valueObjectClass = static::valueObjects()->get($key);
        $this->values->put($key, new $valueObjectClass($value));
    }
    
    public function raw($name)
    {
        return $this->values->get($name);
    }

    public function clone()
    {
        $values = $this->values->each(function ($item, $key) {
            if ($item instanceof GroupedValueObject) {
                return $item->clone();
            }
            return $item->value;
        });
        $class = get_class($this);
        return new $class(new AggregateRootId($this->getAggregateRootId()), $values);
    }
}
