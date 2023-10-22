<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use App\Models\Page;

use App\Jobs\ExplorePage;

class ExploreSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:explore-site {siteId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Explore site (crawl all pages and find all links)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $siteId = $this->argument('siteId');
        $site = Site::where('id', '=', $siteId)->first();

        if ($site == null) {
            print 'Site ' . $siteId . ' not found! Exit.' . PHP_EOL;
        }
        print 'Starting explore site ' . $siteId . ' ' . $site->url . PHP_EOL;

        $page = Page::firstOrCreate(
            ['site_id' => $siteId],
            ['path' => '/']
        );

        try {
            ExplorePage::dispatchSync($page);
        } catch (\Throwable $e) {
            print 'Starting explore site ' . $siteId . ' ' . $site->url . PHP_EOL;
        }

    }
}
