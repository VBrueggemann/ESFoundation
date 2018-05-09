<?php
namespace ESFoundation\ES;

use ESFoundation\ES\ValueObjects\AggregateRootId;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

abstract class DomainEvent extends StorageEvent
{
    protected $aggregateRootId;

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
        return serialize($this->payload->toJson());
    }

    public static function deserialize(DomainStorageEvent $event)
    {
        $domainEvent = new $event->class(
            new AggregateRootId($event->getAggregateRootId()),
            collect(json_decode(unserialize($event->getPayload())))
        );
        $domainEvent->playhead = $event->getPlayhead();
        $domainEvent->createdAt = $event->getCreatedAt();
        $domainEvent->id = $event->getId();
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
     * @param $property
     * @return mixed
     */
    public function __get($property) {
        if ($this->payload->has($property)) {
            return $this->payload->get($property);
        }
    }
}