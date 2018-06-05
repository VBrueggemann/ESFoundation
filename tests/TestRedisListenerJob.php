<?php

namespace tests;

use App\Jobs\Job;
use ESFoundation\ES\Contracts\EventListener;
use ESFoundation\ES\DomainEvent;
use ESFoundation\ES\DomainStorageEvent;
use ESFoundation\ES\ValueObjects\AggregateRootId;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TestRedisListenerJob extends Job
{
    private $eventListener;
    private $aggregateRootId;

    function __construct(EventListener $eventListener, AggregateRootId $aggregateRootId = null)
    {
        $this->eventListener = $eventListener;
        $this->aggregateRootId = $aggregateRootId;
    }

    public function handle()
    {
        Log::info('Started Listening for ' . ($this->aggregateRootId ? $this->aggregateRootId->value : 'all'));

        $redis = Redis::connection();
        $redis->subscribe(($this->aggregateRootId ? $this->aggregateRootId->value : 'all'),
            function ($domainEvent) {
                $this->eventListener->handle(
                    DomainEvent::deserializePayload(
                        DomainStorageEvent::fromJson(
                            $this->aggregateRootId ?: new AggregateRootId(json_decode(unserialize($domainEvent), true)['aggregate_root_id']),
                            unserialize($domainEvent)
                        )
                    )
                );
            }
        );
    }
}