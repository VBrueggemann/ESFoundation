<?php

namespace ESFoundation\ES;

interface AggregateRoot
{
    public function applyThat(DomainEventStream $event): bool;

    public function popUncommittedEvents(): DomainEventStream;

    public function getAggregateRootId(): string;

    public function getPlayhead(): int;

    public static function initialize(DomainEventStream $domainEventStream): AggregateRoot;
}