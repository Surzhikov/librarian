<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Page extends Model
{
    use HasFactory;

    protected $table = 'pages';
    protected $guarded = ['*'];
    public $timestamps = false;
    
    public $fillable = [
        'site_id',
        'path'
    ];
    
    public $hidden = [];
    
    public $appends = [];

    /**
     * Related site
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }

    /**
     * Related resources 
     */
    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'pages_resources', 'page_id', 'resource_id');
    }

    /**
     * Full url for this page 
     */
    public function getUrlAttribute()
    {
        return $this->site->url . $this->path;
    }

    /**
     * Scheme of page 
     */
    public function getSchemeAttribute()
    {
        return parse_url($this->url, PHP_URL_SCHEME);
    }

    /**
     * Query component of Host
     */
    public function getHostAttribute()
    {
        return parse_url($this->url, PHP_URL_HOST);
    }

    /**
     * Query component of URL
     */
    public function getQueryAttribute()
    {
        return parse_url($this->url, PHP_URL_QUERY);
    }

    /**
     * Query component of URL
     */
    public function getPathDirAttribute()
    {
        $pathInfo = pathinfo($this->path);

        if (isset($pathInfo['extension'])) {
            return $pathInfo['dirname'] . '/';
        } else if (mb_substr($this->path, -1) == '/') {
            return $this->path;
        }
        
        return $this->path . '/';
    }
}