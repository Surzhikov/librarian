<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $table = 'resources';
    protected $guarded = ['*'];
    public $timestamps = false;
    
    public $fillable = [
        'scheme',
        'host',
        'path',
        'query',
        'fragment',
        'mime',
        'status_code',
        'visited_at',
    ];
    
    public $hidden = [];
    
    public $appends = [];


    /**
     * 
     */
    public function getUrlAttribute()
    {
        $url = '';
        if (isset($this->scheme)) {
            $url .= $this->scheme . ':';
            if (!in_array($this->scheme, ['mailto', 'tel', 'data'])) {
                $url .= '//';
            }
        }

        $url.= (isset($this->host) ? $this->host : '');
        $url.= (isset($this->path) ? $this->path : '');
        $url.= (isset($this->query) ? '?' . $this->query : '');
        $url.= (isset($this->fragment) ? '#' . $this->fragment : '');

        return $url;
    }


}
