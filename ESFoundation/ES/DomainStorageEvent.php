<?php

namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\AggregateRootId;
use ESFoundation\ES\ValueObjects\DomainEventId;
use Illuminate\Support\Carbon;

class DomainStorageEvent extends StorageEvent
{
    protected $aggregateRootId;
    protected $payload;
    public $class;

    /**
     * DomainStorageEvent constructor.
     * @param $aggregateRootId
     * @param $id
     * @param $createdAt
     * @param $playhead
     * @param $payload
     * @param $class
     */
    public function __construct(AggregateRootId $aggregateRootId, DomainEventId $id, Carbon $createdAt, int $playhead, $payload, string $class)
    {
        parent::__construct($id, $createdAt, $playhead);

        $this->aggregateRootId = $aggregateRootId;
        $this->payload = $payload;
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getAggregateRootId()
    {
        return $this->aggregateRootId;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}