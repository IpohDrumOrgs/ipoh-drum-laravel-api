<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
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
    public function ticket()
    {
        return $this->belongsTo('App\Ticket');
    }
    /**
    * 
    */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
