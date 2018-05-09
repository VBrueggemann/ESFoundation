<?php

namespace ESFoundation\ES;

class StorageEvent
{
    protected $id;
    protected $createdAt;
    protected $playhead;
    protected $payload;

    /**
     * StorageEvent constructor.
     * @param $id
     * @param $createdAt
     * @param $playhead
     * @param $payload
     * @param $class
     */
    public function __construct($id, $createdAt, $playhead, $payload)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->playhead = $playhead;
        $this->payload = $payload;
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
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}