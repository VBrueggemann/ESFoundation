<?php

namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\AggregateRootId;

interface EventStore
{
    public function push(DomainEvent $domainEvent);

    public function get(AggregateRootId $aggregateRootId, int $playhead);
}