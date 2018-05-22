<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\DomainEventStream;

interface EventBus
{
    public function dispatch(DomainEventStream $command);

    public function subscribe(EventListener $eventListener, DomainEvent $event = null);
}