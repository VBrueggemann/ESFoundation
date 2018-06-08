<?php

namespace ESFoundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Ramsey\Uuid\Uuid;
use Tests\CreatesTeams;

class CreateCommandHandler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:commandHandler
                            {--N|name=CommandHandler : Class Name}
                            {--C|command=* : Commands handled by this Class}
                            {--P|path=} : Path to Class File
                            {--NS|namespace= : Namespace for Class}
                            {--E|eventBus} : Place EventBus in Constructor
                            {--A|aggregateProjectionRepository} : Place AggregateProjectionRepository in Constructor
                            {--F|force : Overwrite all files if necessary}'
    ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new CommandHandler';

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
        if (! is_dir($directory = app()->path() . DIRECTORY_SEPARATOR . ($this->option('path') ?: 'ES' . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR . 'Handlers'))) {
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

        if (empty($this->option('command')) || ! $this->confirm("Do you want to create the specified Commands?")) {
            return;
        }

        foreach ($this->option('command') as $command) {
            $this->call('make:command', [
                '--name' => $command,
                '--force' => $this->option('force')
            ]);
        }
    }

    protected function compileStub()
    {
        $compiledStub = file_get_contents(__DIR__.'/stubs/CommandHandler.stub');

        $compiledStub = str_replace('{{name}}', $this->option('name'), $compiledStub);
        $compiledStub = str_replace('{{namespace}}', $this->option('namespace') ?? app()->getNamespace(), $compiledStub);
        $compiledStub = str_replace('{{namespace}}', $this->option('namespace') ?? app()->getNamespace(), $compiledStub);

        $imports = '';
        if ($this->option('eventBus')) {
            $imports = $imports . '
use ESFoundation\ES\Contracts\EventBus;';
        }

        if ($this->option('aggregateProjectionRepository')) {
            $imports = $imports . '
use ESFoundation\ES\Contracts\AggregateProjectionRepository;';
        }

        $compiledStub = str_replace('{{imports}}', $imports, $compiledStub);

        $content =
            ($this->option('eventBus') ? 'private $eventBus;
    ' : '') .
            ($this->option('aggregateProjectionRepository') ? 'private $aggregateProjectionRepository;
    ' : '');

        $content = $content . '
    public function __construct(' . ($this->option('eventBus') ? 'EventBus $eventBus' : '') .
            ($this->option('aggregateProjectionRepository') ? ($this->option('eventBus') ? ', ' : '') . 'AggregateProjectionRepository $aggregateProjectionRepository' : '') . ')
    {' . ($this->option('eventBus') ? '
        $this->eventBus = $eventBus;' : '') . ($this->option('aggregateProjectionRepository') ? '
        $this->aggregateProjectionRepository = $aggregateProjectionRepository;' : '') . '
    }';

        foreach ($this->option('command') as $command) {
            $content = $content . '
            
    public function handle' . $command . '(' . $command . ' ' . '$command)
    {
    
    }';
        }


        $compiledStub = str_replace('{{content}}', $content, $compiledStub);
        return $compiledStub;
    }
}
