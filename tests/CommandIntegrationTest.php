<?php

class CommandIntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function a_command_can_recieve_a_payload()
    {
        $command = new \tests\TestCommand(collect([
            'first' => 'one',
            'second'=> 'two'
        ]));

        $this->assertEquals('one', $command->first);
        $this->assertEquals('two', $command->second);

        $this->assertNull($command->third);
    }

    /**
     * @test
     */
    public function a_command_can_recieve_a_empty_payload()
    {
        $command = new \tests\TestCommand();

        $this->assertNull($command->first);
    }

    /**
     * @test
     */
    public function a_command_validates_its_payload()
    {
        $this->expectException(\ESFoundation\CQRS\Errors\InvalidCommandPayloadException::class);
        new \tests\TestCommand(collect([
            'first' => 'one',
            'second'=> 'two'
        ]), [
            'first' => 'required|string',
            'second'=> 'numeric'
        ]);
    }

    /**
     * @test
     */
    public function a_command_takes_only_those_parameters_specified_in_the_rules()
    {
        $command = new \tests\TestCommand(collect([
            'first' => 'one',
            'second'=> 'two'
        ]), [
            'first' => 'required|string',
        ]);

        $this->assertNull($command->second);
    }


    /**
     * @test
     */
    public function a_command_takes_non_array_parameters_and_validate()
    {
        $this->expectException(\ESFoundation\CQRS\Errors\InvalidCommandPayloadException::class);

        new \tests\TestCommand(null , 'required');
    }


    /**
     * @test
     */
    public function a_command_takes_non_array_parameters()
    {
        $command = new \tests\TestCommand('yay' , 'required|string');

        $this->assertEquals('yay', $command->getPayload()[0]);
    }

    /**
     * @test
     */
    public function a_command_takes_non_array_parameters_without_rules()
    {
        $command = new \tests\TestCommand('yay');

        $this->assertEquals('yay', $command->getPayload()[0]);
    }
}
