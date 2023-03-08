<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mail';

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
        Mail::send([], [], function ($message) {
            $message->to('wiktor8555@gmail.com')
                ->subject('Test mail')
                ->setBody('<h1>Hi, welcome user!</h1>', 'text/html');
        });
    }
}
