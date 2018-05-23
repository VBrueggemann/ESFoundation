<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\ValueObjects\AggregateRootValueObject;

interface AggregateRoot
{
    public static function applyThat(DomainEventStream $domainEventStream, AggregateRootValueObject $aggregateRootValueObject): void;

    public static function initialize(DomainEventStream $domainEventStream, bool $withValidation = false): AggregateRootValueObject;

    public static function represent(DomainEventStream $domainEventStream,
        AggregateRootValueObject $aggregateRootValueObject,
        bool $pushToUncommittedEvents = true
    ): void;

    public static function validate(DomainEventStream $domainEventStream, AggregateRootValueObject $aggregateRootValueObject): void;
}