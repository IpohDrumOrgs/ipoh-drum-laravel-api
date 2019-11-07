<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
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
    public function sales()
    {
        return $this->hasMany('App\Sale');
    }
    /**
     * 
     */
    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    
    /**
     * 
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
