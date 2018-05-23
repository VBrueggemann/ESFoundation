<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

interface AggregateRoot
{
    public static function applyThat(DomainEventStream $domainEventStream, AggregateRootProjection $AggregateRootProjection): void;

    public static function initialize(DomainEventStream $domainEventStream, bool $withValidation = false): AggregateRootProjection;

    public static function represent(DomainEventStream $domainEventStream,
        AggregateRootProjection $AggregateRootProjection,
        bool $pushToUncommittedEvents = true
    ): void;

    public static function validate(DomainEventStream $domainEventStream, AggregateRootProjection $AggregateRootProjection): void;
}