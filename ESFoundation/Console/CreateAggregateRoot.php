<?php

namespace ESFoundation\Console;

use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;
use Tests\CreatesTeams;

class CreateAggregateRoot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:aggregateRoot
                            {--N|name=AggregateRoot : Class Name}
                            {--P|path=} : Path to Class File
                            {--NS|namespace= : Namespace for Class}
                            {--E|event=* : Applicable Event}'
    ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new AggregateRoot';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! is_dir($directory = app()->path() . DIRECTORY_SEPARATOR . ($this->option('path') ?:
                'ES' . DIRECTORY_SEPARATOR . 'Events' . DIRECTORY_SEPARATOR . 'Aggregates'))) {
            mkdir($directory, 0777, true);
        }

        if (file_exists($directory . DIRECTORY_SEPARATOR . $this->option('name') . '.php')) {
            if (! $this->confirm("The  file [{$this->option('name')}] already exists. Do you want to replace it?")) {
                return;
            }
        }

        file_put_contents(
            $directory . DIRECTORY_SEPARATOR . $this->option('name') . '.php',
            $this->compileStub()
        );
    }

    protected function compileStub()
    {
        $compiledStub = file_get_contents(__DIR__.'/stubs/AggregateRoot.stub');

        $compiledStub = str_replace('{{name}}', $this->option('name'), $compiledStub);
        $compiledStub = str_replace('{{namespace}}', $this->option('namespace') ?? app()->getNamespace(), $compiledStub);

        $content = '';
        foreach ($this->option('event') as $event) {
             $content = $content . '
     public function applyThat' . $event .'(' . $event . ' $event, AggregateRootProjection $aggregateRootProjection)
     {
     
     }
     ';
        }

        $compiledStub = str_replace('{{content}}', $content, $compiledStub);

        return $compiledStub;
    }
}
