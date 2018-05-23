<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\ValueObjects\AggregateRootValueObject;

interface AggregateRootValidator
{
    public static function validate(AggregateRootValueObject $aggregateRoot, DomainEvent $domainEvent): bool;
}