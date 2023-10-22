<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Date;

use App\Models\Page;
use App\Models\Resource;

class ExplorePage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Page $page;
    private array $internalPages = [];


    /**
     * Create a new job instance.
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        print '--------------------------------' . PHP_EOL;

        if ($this->page->visited_at != null) {
            $visitedTime = Date::parse($this->page->visited_at);
            if (Date::now()->diffInHours($visitedTime) < 1) {
                print "The page «" . $this->page->url . "» was visited. Skip" . PHP_EOL;
                return;
            }
        }

        print 'Explore Page ' . $this->page->url . PHP_EOL;

        $response = Http::timeout(5)->get($this->page->url);
        $this->page->visited_at = Date::now();
        $this->page->status_code = $response->status();
        $this->page->save();

        print '- Status code: ' . $this->page->status_code . PHP_EOL;
        if ($response->failed()) {
            throw new \Exception("Request failed");
        }


        // Scrap links with Regular expressions
        $html = $response->body();
        $patternHref = '/href=["\'](.*?)["\']/u';
        $patternSrc = '/src=["\'](.*?)["\']/u';

        $resourcesArr = [];

        preg_match_all($patternHref, $html, $matches_href);
        foreach ($matches_href[1] as $url) {
            $resourcesArr[]= $this->clearUrl($url);
        }

        preg_match_all($patternSrc, $html, $matches_src);
        foreach ($matches_src[1] as $url) {
            $resourcesArr[]= $this->clearUrl($url);
        }



        print PHP_EOL;
        print PHP_EOL;
        print 'Resources links found:' . PHP_EOL;

        foreach($resourcesArr as $resourceUrlArr) {

            // If this is not a http / https / ftp link – skip it
            if (in_array($resourceUrlArr['scheme'], ['http', 'https', 'ftp']) == false) {
                continue;
            }

            if (array_key_exists('host', $resourceUrlArr) == false) {
                continue;
            }

            if ($this->isInternalPage($resourceUrlArr)) {
                $page = Page::firstOrCreate([
                    'site_id' => $this->page->site_id,
                    'path' => $resourceUrlArr['path'],
                ], [
                    'site_id' => $this->page->site_id,
                    'path' => $resourceUrlArr['path'],
                ]);
                $this->internalPages[]= $page;
                continue;
            }

            $resource = Resource::firstOrCreate(
                [
                    'scheme' => $resourceUrlArr['scheme'],
                    'host' => $resourceUrlArr['host'],
                    'path' => $resourceUrlArr['path'] ?? null,
                    'query' => $resourceUrlArr['query'] ?? null,
                    'fragment' => $resourceUrlArr['fragment'] ?? null,
                ],
                [
                    'scheme' => $resourceUrlArr['scheme'],
                    'host' => $resourceUrlArr['host'],
                    'path' => $resourceUrlArr['path'] ?? null,
                    'query' => $resourceUrlArr['query'] ?? null,
                    'fragment' => $resourceUrlArr['fragment'] ?? null,
                ]
            );

            print '→ ' . $resource->url . PHP_EOL;

            if ($resource->wasRecentlyCreated) {
                print ' ✓ Created!' . PHP_EOL;
            } else {
                print ' ✓ Already in DB' . PHP_EOL;
            }

            $this->page->resources()->attach($resource);
            print ' ✓ Resource attached to the Page!' . PHP_EOL;

            print PHP_EOL;
        }
        print PHP_EOL;



        print 'Explore other internal pages' . PHP_EOL;
        foreach ($this->internalPages as $page) {
            try {
                ExplorePage::dispatchSync($page);
            } catch (\Throwable $e) {
                print "Error, while explore a page " . $page->path . PHP_EOL;
                print $e->getMessage() . ' on line ' . $e->getLine() . PHP_EOL;
            }
        }

    }


    /**
     * Clear url
     */
    private function clearUrl($url)
    {
        $urlArr = parse_url($url);

        if (!array_key_exists('scheme', $urlArr)) {
            $urlArr['scheme'] = $this->page->scheme;
        }

        if (in_array($urlArr['scheme'], ['http', 'https']) == false) {
            return $urlArr;
        }

        if (!array_key_exists('host', $urlArr)) {
            $urlArr['host'] = $this->page->host;
        }

        //print '★★★ URL = ' . $url  . PHP_EOL;
        //print '★★★ pagePathDir = ' . $this->page->path_dir . PHP_EOL;


        // Если есть хост, но нет пути, или путь не начинается со слеша, добавим слеш
        if (array_key_exists('host', $urlArr) && (!array_key_exists('path', $urlArr) || strpos($urlArr['path'], '/') !== 0)) {
            $urlArr['path'] = $this->page->path_dir  . ($urlArr['path'] ?? '');
        }


        $urlArr['path'] = $this->collapsePath($urlArr['path']);


        //print_r($urlArr);
        return $urlArr; 
    }


    /**
     * Check if this internal page. 
     */
    private function isInternalPage($urlParts) {
        if (isset($urlParts['scheme'], $urlParts['host']) && $urlParts['host'] === $this->page->host) {
            
            if (!isset($urlParts['path']) || empty($urlParts['path']) || $urlParts['path'] === '/') {
                return true;
            }

            $pathInfo = pathinfo($urlParts['path']);

            if (!isset($pathInfo['extension']) || in_array($pathInfo['extension'], ['html', 'htm', 'asp', 'aspx', 'php'])) {
                return true;
            }
        }

        return false;
    }


    private function collapsePath($path) {
        $parts = array_filter(explode("/", $path), 'strlen');
        $absolutes = array();
        
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        
        return '/' . implode("/", $absolutes);
    }



}
