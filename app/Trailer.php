<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trailer extends Model
{
    
    public function video()
    {
        return $this->belongsTo('App\Video');
    }
}
