<?php

namespace Olajide\Console\Commands;

use Illuminate\Console\Command;

class Crawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl {--site=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl websites';

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
     * @return mixed
     */
    public function handle()
    {
        $site = $this->option('site');
        // check if site exists

        $class = 'Olajide\Crawler\\'.ucwords($site);
        if(!class_exists($class)) {
            return 'Sorry that site does not exists...';
        }

        return new $class();
    }
}
