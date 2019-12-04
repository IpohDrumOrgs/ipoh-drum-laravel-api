<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPromotion extends Model
{
    
    /**
    * 
    */
    public function inventories()
    {
        return $this->hasMany('App\Inventory');
    }
    
    /**
    * 
    */
    public function tickets()
    {
        return $this->hasMany('App\Ticket');
    }
    
    /**
    * 
    */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
