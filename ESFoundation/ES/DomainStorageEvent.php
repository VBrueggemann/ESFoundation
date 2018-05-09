<?php

namespace ESFoundation\ES;

class DomainStorageEvent extends StorageEvent
{
    protected $aggregateRootId;
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
        parent::__construct($id, $createdAt, $playhead, $payload);

        $this->aggregateRootId = $aggregateRootId;
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getAggregateRootId()
    {
        return $this->aggregateRootId;
    }
}