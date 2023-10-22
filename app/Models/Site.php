<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Site extends Model
{
    use HasFactory;

    protected $table = 'sites';
    protected $guarded = ['*'];
    public $timestamps = false;
    
    public $fillable = [

    ];
    
    public $hidden = [];
    
    public $appends = [];



}
