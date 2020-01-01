<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    
    public function coverimage()
    {
        return $this->hasOne('App\ArticleImage', 'cover_image_id');
    }

    public function articleimages()
    {
        return $this->hasMany('App\ArticleImage');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function blogger()
    {
        return $this->belongsTo('App\Blogger');
    }
}
