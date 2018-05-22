<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\DomainEvent;

interface EventListener
{
    public function handle(DomainEvent $domainEvent);
}