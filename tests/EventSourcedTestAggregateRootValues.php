<?php

namespace tests;

use ESFoundation\ES\ValueObjects\AggregateRootProjection;
use ESFoundation\ES\ValueObjects\TestValueObject;
use Illuminate\Support\Collection;

class EventSourcedTestAggregateRootValues extends AggregateRootProjection
{
    public static function valueObjects(): Collection
    {
        return collect([
            'first'  => TestValueObject::class,
            'second' => TestValueObject::class
        ]);
    }
}