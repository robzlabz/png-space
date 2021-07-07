<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadImageJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $name;
    protected $tags;
    protected $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user,  $url, $name, $tags)
    {
        $this->user = $user;
        $this->name = $name;
        $this->tags = $tags;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->user->addImage(
            $this->url,
            $this->name,
            $this->tags
        );
    }
}
