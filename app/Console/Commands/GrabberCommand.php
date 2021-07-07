<?php

namespace App\Console\Commands;

use App\Jobs\AJob;
use App\Jobs\BlowJob;
use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class GrabberCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grab:start';

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
        $item = Image::first();

        AJob::withChain([new BlowJob])->dispatch();

        dd($item);
        return 0;
    }
}
