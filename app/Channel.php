<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{

    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    
    public function user()
    {
        return $this->belongsTo('App\User');
    }
   
    public function videos()
    {
        return $this->hasMany('App\Video');
    }
    
    public function playlists()
    {
        return $this->hasMany('App\PlayList');
    }
}
