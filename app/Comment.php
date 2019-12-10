<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    
    public function secondcomments()
    {
        return $this->hasMany('App\SecondComment');
    }
   
    public function video()
    {
        return $this->belongsTo('App\Video');
    }
    
    public function article()
    {
        return $this->belongsTo('App\Article');
    }

    public function articleimage()
    {
        return $this->belongsTo('App\ArticleImage');
    }
    
}
