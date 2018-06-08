<?php
namespace App\ES\Events\Aggregates;

use ESFoundation\ES\Contracts as AggregateRootValidatorContract;
use ESFoundation\ES\ValueObjects\AggregateRootProjection as AggregateRootProjectionContract;
use ESFoundation\ES\DomainEvent;
use Illuminate\Support\Collection;

class AggregateRootValues extends AggregateRootProjectionContract
{
    public static function valueObjects(): Collection
    {
        return collect([

        ]);
    }
}