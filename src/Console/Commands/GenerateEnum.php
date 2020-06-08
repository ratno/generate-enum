<?php

namespace Ratno\GenerateEnum\Console\Commands;

use Illuminate\Console\Command;

class GenerateEnum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:enum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate enumerasi pada model tertentu berdasarkan config yang di set';

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
        $generateEnum = new \Ratno\GenerateEnum\GenerateEnum();
        $generateEnum->proses();
    }
}
