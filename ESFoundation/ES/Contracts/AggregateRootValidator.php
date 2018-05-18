<?php

namespace ESFoundation\ES;

interface AggregateRootValidator
{
    public static function validate(AggregateRoot $aggregateRoot, DomainEvent $domainEvent): bool;
}