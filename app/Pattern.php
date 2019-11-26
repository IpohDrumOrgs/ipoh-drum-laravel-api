<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pattern extends Model
{

    public function inventoryfamily()
    {
        return $this->belongsTo('App\InventoryFamily', 'inventory_family_id');
    }
}
