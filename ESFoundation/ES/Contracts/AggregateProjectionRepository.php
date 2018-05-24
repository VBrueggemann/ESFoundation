<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\AggregateRootProjection;

interface AggregateProjectionRepository
{
    public function load(AggregateRootId $aggregateRootId, string $aggregateRootClass, int $playhead = 0): ?AggregateRootProjection;
}