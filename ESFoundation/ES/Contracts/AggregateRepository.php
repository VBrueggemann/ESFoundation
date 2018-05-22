<?php

namespace ESFoundation\ES\Contracts;

use ESFoundation\ES\ValueObjects\AggregateRootId;

interface AggregateRepository
{
    public function load(AggregateRootId $aggregateRootId, string $aggregateRootClass, int $playhead = 0): ?AggregateRoot;
}