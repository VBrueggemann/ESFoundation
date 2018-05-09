<?php

class EventIntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function an_event_can_have_a_payload()
    {
        $event = new \tests\TestEvent(
            new \ESFoundation\ES\ValueObjects\AggregateRootId(\Ramsey\Uuid\Uuid::uuid4()->toString()),
            collect([
                'first' => 'one',
                'second' => 'two'
            ])
        );

    }
}
