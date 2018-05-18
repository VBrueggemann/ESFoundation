<?php

namespace tests;

use ESFoundation\ES\AggregateRoot;
use ESFoundation\ES\AggregateRootValidator;
use ESFoundation\ES\DomainEvent;

class EventSourcedTestAggregateRootValidator implements AggregateRootValidator
{
    public static function validate(AggregateRoot $aggregateRoot, DomainEvent $domainEvent): bool
    {
        return $domainEvent->getPayload()->first() !== 'test';
    }
}