<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use App\Models\Page;
use App\Models\Resource;

use App\Jobs\ExploreResource;

class ExploreResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:explore-resources {siteId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Explore site / all resources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $siteId = $this->argument('siteId');
        if ($siteId != null) {
            $site = Site::where('id', '=', $siteId)->first();

            if ($site == null) {
                print 'Site ' . $siteId . ' not found! Exit.' . PHP_EOL;
            }

            print 'Starting explore resources of site id ' . $siteId . ' «' . $site->url  . '»...' . PHP_EOL;

            $pages = Page::where('site_id', '=', $site->id)->get();
            $resources = collect();
            $pages->each(function($page) use (&$resources) {
                $pageResources = $page->resources;
                $resources = $resources->concat($pageResources);
            });

            $resources = $resources->sortBy('id')->unique('id');
        } else {
            print 'Starting explore all resources...' . PHP_EOL;
            $resources = Resource::all();
        }

        foreach ($resources as $resource) {
            try {
                ExploreResource::dispatchSync($resource);
            } catch (\Throwable $e) {
                print 'Error while explore resource' . PHP_EOL;
                print $e->getMessage() . ' on line ' . $e->getLine() . PHP_EOL;
            }
        }
    }


}
