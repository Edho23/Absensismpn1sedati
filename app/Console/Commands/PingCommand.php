<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PingCommand extends Command
{
    protected $signature = 'demo:ping';
    protected $description = 'Ping command to verify scheduler/commands discovery';

    public function handle(): int
    {
        $this->info('PONG @ '.now()->toDateTimeString());
        return self::SUCCESS;
    }
}
