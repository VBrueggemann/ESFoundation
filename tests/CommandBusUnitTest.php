<?php

class CommandBusUnitTest extends TestCase
{
    /**
     * @test
     */
    public function a_command_bus_dispatches_a_command_to_a_subscriber()
    {
        $command = new \tests\TestCommand(collect([
            'first' => 'one',
            'second'=> 'two'
        ]));

        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();

        $commandHandler = new \tests\TestCommandHandler();

        $commandBus->subscribe($commandHandler);

        $commandBus->dispatch($command);

        $this->assertEquals([get_class($command)], $commandHandler->getHandledCommands());
    }

    /**
     * @test
     */
    public function a_command_bus_dispatches_multiple_commands_to_a_subscriber()
    {
        $command = new \tests\TestCommand(collect([
            'first' => 'one',
            'second'=> 'two'
        ]));

        $commandBus = new \ESFoundation\CQRS\InMemorySynchronusCommandBus();

        $commandHandler = new \tests\TestCommandHandler();

        $commandBus->subscribe($commandHandler);

        $commandBus->dispatch($command);
        $commandBus->dispatch($command);

        $this->assertEquals([get_class($command), get_class($command)], $commandHandler->getHandledCommands());
    }
}
