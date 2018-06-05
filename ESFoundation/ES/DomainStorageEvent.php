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
    public function __construct(AggregateRootId $aggregateRootId, DomainEventId $id, Carbon $createdAt, int $playhead, array $payload, string $class)
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

    public function toJson(bool $withAggregateRootId)
    {
        return '{"id":' . json_encode($this->id->value) . ',"playhead":"' . $this->playhead . '","payload":' . json_encode($this->payload) . ',"created_at":' . json_encode($this->createdAt->toW3cString()) . ',"class":"' . str_replace('\\', '\\\\', $this->class) . '"' . ($withAggregateRootId ? ',"aggregate_root_id":"' . $this->aggregateRootId->value . '"' : '') . '}';
    }

    public static function fromJson(AggregateRootId $aggregateRootId, string $json)
    {
        $json = json_decode($json, true);
        return new self(
            $aggregateRootId,
            new DomainEventId($json['id']),
            Carbon::make($json['created_at']),
            $json['playhead'],
            $json['payload'],
            $json['class']
        );
    }
}