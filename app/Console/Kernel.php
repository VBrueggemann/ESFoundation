<?php

namespace App\Console;

use ESFoundation\Console\CreateAggregateRoot;
use ESFoundation\Console\CreateAggregateRootProjection;
use ESFoundation\Console\CreateAggregateRootValidator;
use ESFoundation\Console\CreateCommand;
use ESFoundation\Console\CreateCommandHandler;
use ESFoundation\Console\CreateEvent;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CreateCommandHandler::class,
        CreateCommand::class,
        CreateAggregateRoot::class,
        CreateEvent::class,
        CreateAggregateRootValidator::class,
        CreateAggregateRootProjection::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
