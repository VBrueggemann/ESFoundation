<?php

namespace ESFoundation\ES;

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
    public function __construct($id, $createdAt, $playhead)
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
}