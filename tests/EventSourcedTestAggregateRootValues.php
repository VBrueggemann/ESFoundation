<?php

namespace tests;

use ESFoundation\ES\ValueObjects\AggregateRootValueObject;
use ESFoundation\ES\ValueObjects\TestValueObject;
use Illuminate\Support\Collection;

class EventSourcedTestAggregateRootValues extends AggregateRootValueObject
{
    public static function valueObjects(): Collection
    {
        return collect([
            'first'  => TestValueObject::class,
            'second' => TestValueObject::class
        ]);
    }
}