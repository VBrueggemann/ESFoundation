<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\DomainEvent;

interface AggregateRootValidator
{
    public static function validate(AggregateRoot $aggregateRoot, DomainEvent $domainEvent): bool;
}