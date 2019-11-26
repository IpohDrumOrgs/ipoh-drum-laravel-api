<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryFamily extends Model
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
    public function saleitems()
    {
        return $this->hasMany('App\SaleItem');
    }
    
    /**
     *
     */
    public function patterns()
    {
        return $this->hasMany('App\Pattern');
    }
}
