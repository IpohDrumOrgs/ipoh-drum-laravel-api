<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCharacteristic extends Model
{
    
    /**
     *
     */
    public function inventories()
    {
         return $this->belongsToMany('App\ProductCharacteristic','inventory_product_characteristic', 'characteristic_id' , 'inventory_id' )->withPivot('remark');

    }
    /**
     *
     */
    public function tickets()
    {
        return $this->belongsToMany('App\ProductCharacteristic','inventory_product_characteristic', 'characteristic_id' , 'ticket_id' )->withPivot('remark');
    }
}
