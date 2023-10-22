<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

use App\Models\Resource;

class ExploreResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Resource $resource;

    /**
     * Create a new job instance.
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        print 'HEAD ' . $this->resource->url . PHP_EOL;
        
        try {
            $response = Http::timeout(10)->head($this->resource->url);
            $this->resource->status_code = $response->status();
        } catch (\Throwable $e) {
            $this->resource->status_code = 404;
        }
        
        $this->resource->visited_at = Date::now();
        $this->resource->save();

        print '- Status code: ' . $this->resource->status_code . PHP_EOL;

    }
}
