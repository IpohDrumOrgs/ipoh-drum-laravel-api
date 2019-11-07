<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'cost' => 'float',
        'price' => 'float',
        'stock' => 'integer',
        'salesqty' => 'integer',
        'stockthreshold' => 'integer',
        'backorder' => 'boolean',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the store of the inventory.
     */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }


    /**
     * Get the saleitems of the inventory.
     */
    public function saleitems()
    {
        return $this->hasMany('App\SaleItem');
    }

    
    /**
     * 
     */
    public function categories()
    {
        return $this->belongsToMany('App\Category')->withPivot('status','remark');
    }
    /**
     * 
     */
    public function types()
    {
        return $this->belongsToMany('App\Type')->withPivot('status','remark');
    }
    /**
     * 
     */
    public function productfeatures()
    {
        return $this->belongsToMany('App\ProductFeature')->withPivot('status','remark');
    }
}
