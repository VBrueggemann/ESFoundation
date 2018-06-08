<?php

namespace tests;

use ESFoundation\ES\ValueObjects\AggregateRootProjection;
use ESFoundation\ES\ValueObjects\TestValueObject;
use Illuminate\Support\Collection;

class EventSourcedTestAggregateRootProjection extends AggregateRootProjection
{
    public static function valueObjects(): Collection
    {
        return collect([
            'first'  => TestValueObject::class,
            'second' => TestValueObject::class
        ]);
    }
}