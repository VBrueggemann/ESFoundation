<?php

namespace ESFoundation\ES;

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
    public function __construct($aggregateRootId, $id, $createdAt, $playhead, $payload, $class)
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