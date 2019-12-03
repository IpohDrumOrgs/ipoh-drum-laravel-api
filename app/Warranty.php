<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
     /**
    * 
    */
    public function inventory()
    {
        return $this->belongsTo('App\Inventory');
    }
    /**
    * 
    */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
