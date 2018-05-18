<?php

namespace ESFoundation\ES;

interface EventBus
{
    public function dispatch(DomainEventStream $command);

    public function subscribe(EventListener $commandHandler, DomainEvent $command = null);
}