<?php

namespace GuillermoRod\StringToFile\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuillermoRod\StringToFile\ServiceProvider;
use GuillermoRod\StringToFile\Services\FileCreatorService;

class PerformStringToFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $owner;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    public function __construct($owner) 
    {
        $this->owner = $owner;
        $this->queue = config(ServiceProvider::CONFIG_FILE . '.queue_name', 'convert-string-to-file');
    }

    public function handle()
    {
        (new FileCreatorService)->make($this->owner, true);
    }
}