<?php
namespace App\ES\Events\Aggregates;

use ESFoundation\ES\Contracts as AggregateRootValidatorContract;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;
use ESFoundation\ES\DomainEvent;

class AggregateRootValidator implements AggregateRootValidatorContract
{
    public static function validate(AggregateRootProjection $projection, DomainEvent $domainEvent): bool
    {

    }
}