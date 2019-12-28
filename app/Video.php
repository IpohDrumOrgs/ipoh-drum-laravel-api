<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    public function playlist()
    {
        return $this->belongsTo('App\Playlist');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function images()
    {
        return $this->hasMany('App\VideoImage');
    }
}
