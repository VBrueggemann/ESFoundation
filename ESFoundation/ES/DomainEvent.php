<?php
namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\AggregateRootId;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

abstract class DomainEvent
{
    protected $aggregateRootId;
    protected $playhead;
    protected $createdAt;
    protected $payload;
    protected $id;

    /**
     * DomainEvent constructor.
     * @param $aggregateRootId
     * @param $payload
     */
    public function __construct(AggregateRootId $aggregateRootId, $payload)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->aggregateRootId = $aggregateRootId;

        if (!is_object($payload)) {
            $payload = collect([$payload]);
        }

        if (get_class($payload) != Collection::class) {
            $payload = collect($payload);
        }

        $validator = Validator::make($payload->toArray(), is_array($this->rules()) ? $this->rules() : [$this->rules()]);
        throw_if($validator->fails(), Errors\InvalidEventPayloadException::class, $validator->errors());

        $payload = $this->rules() ? $payload->only(collect($this->rules())->keys()) : $payload;

        $this->payload = $payload;
    }

    public function rules()
    {
        return [];
    }

    public function serialize()
    {
        return serialize($this->payload);
    }

    public static function deserialize(EventStoragage $event)
    {
        $domainEvent = new $event->class(new AggregateRootId($event->aggregateRootId), unserialize($event->payload));
        $domainEvent->playhead = $event->playhead;
        $domainEvent->createdAt = $event->createdAt;
        $domainEvent->id = $event->id;
        return $domainEvent;
    }

    /**
     * @return AggregateRootId
     */
    public function getAggregateRootId(): AggregateRootId
    {
        return $this->aggregateRootId;
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
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }
}