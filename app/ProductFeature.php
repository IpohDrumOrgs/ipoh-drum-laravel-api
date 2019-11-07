<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductFeature extends Model
{
    
    /**
     * 
     */
    public function inventories()
    {
        return $this->belongsToMany('App\Inventory')->withPivot('status','remark');
    }
    /**
     * 
     */
    public function tickets()
    {
        return $this->belongsToMany('App\Ticket')->withPivot('status','remark');
    }
}
