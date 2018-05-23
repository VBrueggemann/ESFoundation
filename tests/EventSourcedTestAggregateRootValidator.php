<?php

namespace tests;

use ESFoundation\ES\Contracts\AggregateRootValidator;
use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

class EventSourcedTestAggregateRootValidator implements AggregateRootValidator
{
    public static function validate(AggregateRootProjection $aggregateRootValues, DomainEvent $domainEvent): bool
    {
        return $domainEvent->first !== 'second';
    }
}