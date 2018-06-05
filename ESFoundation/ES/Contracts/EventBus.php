<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\DomainEventStream;
use ESFoundation\ES\ValueObjects\AggregateRootId;

interface EventBus
{
    public function dispatch(DomainEventStream $command);

    public function subscribe(EventListener $eventListener, AggregateRootId $aggregateRootId = null);
}