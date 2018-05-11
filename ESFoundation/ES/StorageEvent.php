<?php

namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\DomainEventId;
use Illuminate\Support\Carbon;

abstract class StorageEvent
{
    protected $id;
    protected $createdAt;
    protected $playhead;

    /**
     * StorageEvent constructor.
     * @param $id
     * @param $createdAt
     * @param $playhead
     * @internal param $class
     */
    public function __construct(DomainEventId $id, Carbon $createdAt, int $playhead)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->playhead = $playhead;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getPlayhead()
    {
        return $this->playhead;
    }

    /**
     * @param Carbon $createdAt
     */
    public function setCreatedAt(Carbon $createdAt)
    {
        $this->createdAt = $this->createdAt ?? $createdAt;
    }
}