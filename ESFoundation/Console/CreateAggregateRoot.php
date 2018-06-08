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
                            {--S|namespace= : Namespace for Class}
                            {--E|event=* : Applicable Event}
                            {--c|createEvent : Create Events for the specified AggregateRoot}
                            {--F|force : Overwrite all files if necessary}
                            {--a|validator : Create Validator for the specified AggregateRoot}
                            {--p|projection : Create Projection for the specified AggregateRoot}'
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

        if (! $this->option('force') && file_exists($directory . DIRECTORY_SEPARATOR . $this->option('name') . '.php')) {
            if (! $this->confirm("The  file [{$this->option('name')}] already exists. Do you want to replace it?")) {
                return;
            }
        }

        file_put_contents(
            $directory . DIRECTORY_SEPARATOR . $this->option('name') . '.php',
            $this->compileStub()
        );

        if (!empty($this->option('event')) && ($this->option('createEvent') || $this->confirm("Do you want to create the specified Events?"))) {
            foreach ($this->option('event') as $event) {
                $this->call('make:event', [
                    '--name' => $event,
                    '--force' => $this->option('force')
                ]);
            }
        }

        if ($this->option('validator') || $this->confirm("Do you want to create the corresponding AggregateRootValidator?")) {
            $this->call('make:aggregateRootValidator', [
                '--name' => $this->option('name') . 'Validator',
                '--force' => $this->option('force')
            ]);
        }

        if ($this->option('projection') || $this->confirm("Do you want to create the corresponding AggregateRootProjection?")) {
            $this->call('make:aggregateRootProjection', [
                '--name' => $this->option('name') . 'Values',
                '--force' => $this->option('force')
            ]);
        }
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
