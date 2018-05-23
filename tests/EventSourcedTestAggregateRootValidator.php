<?php

namespace tests;

use ESFoundation\ES\Contracts\AggregateRootValidator;
use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\ValueObjects\AggregateRootValueObject;

class EventSourcedTestAggregateRootValidator implements AggregateRootValidator
{
    public static function validate(AggregateRootValueObject $aggregateRootValues, DomainEvent $domainEvent): bool
    {
        return $domainEvent->first !== 'second';
    }
}