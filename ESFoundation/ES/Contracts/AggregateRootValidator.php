<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

interface AggregateRootValidator
{
    public static function validate(AggregateRootProjection $aggregateRoot, DomainEvent $domainEvent): bool;
}