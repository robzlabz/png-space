<?php

use App\Jobs\DownloadImageJob;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\LazyCollection;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('download', function () {

    $this->info("Creating Job");
    $batch = Bus::batch([])
        ->name('Download Images')
        ->allowFailures()
        ->finally(function () {
        })->dispatch();

    $users = User::all();

    $this->info("Cursor Images");
    DB::connection('sqlite')
        ->table('images')
        ->cursor()
        ->map(function ($image) use ($users) {
            return new DownloadImageJob($users->shuffle()->first(), $image->image, $image->title, $image->tags);
        })
        ->filter()
        ->chunk(1000)
        ->each(function (LazyCollection $jobs) use ($batch) {
            $this->info("Adding Batch");
            $batch->add($jobs);
        });
});
