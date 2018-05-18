<?php

namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\AggregateRootId;

interface AggregateRepository
{
    public function load(AggregateRootId $aggregateRootId, string $aggregateRootClass, int $playhead = 0): ?AggregateRoot;
}