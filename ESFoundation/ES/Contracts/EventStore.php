<?php

namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\AggregateRootId;

interface EventStore
{
    public function push(DomainEventStream $domainEventStream, $meta = null);

    public function get(AggregateRootId $aggregateRootId, int $playhead = 0): DomainEventStream;
}