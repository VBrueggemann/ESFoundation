<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

interface AggregateRepository
{
    public function load(AggregateRootId $aggregateRootId, string $aggregateRootClass, int $playhead = 0): ?AggregateRootProjection;
}