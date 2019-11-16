<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryImage extends Model
{

    /**
     *
     */
    public function inventory()
    {
        return $this->belongsTo('App\Inventory');
    }
}
