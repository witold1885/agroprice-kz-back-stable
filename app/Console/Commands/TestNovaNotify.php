<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class TestNovaNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testnova:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (Admin::all() as $admin) {
            $admin->notify(
                NovaNotification::make()
                    ->message('Test message')
                    ->type('info')
            );
        }
    }
}
