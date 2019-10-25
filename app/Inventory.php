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
     * Get the company of the inventory.
     */
    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    /**
     * Get the accounts price list of the inventory.
     */
    public function accounts()
    {
        return $this->belongsToMany('App\Account','account_inventory')->withPivot( 'min','price','lastedit_by','created_at','updated_at');
    }

    /**
     * Get the batches of the inventory.
     */
    public function batches()
    {
        return $this->hasMany('App\Batch');
    }

    /**
     * Get the saleitems of the inventory.
     */
    public function saleitems()
    {
        return $this->hasMany('App\SaleItem');
    }

    /**
     * Get the saleitems of the inventory.
     */
    public function inventorybatches()
    {
        return $this->hasMany('App\InventoryBatch');
    }

    /**
     * Get the purchaseitems of the inventory.
     */
    public function purchaseitems()
    {
        return $this->hasMany('App\PurchaseItem');
    }
    
    /**
     * Get the purchaseitems of the inventory.
     */
    public function stocktransfers()
    {
        return $this->belongsToMany('App\Inventory','stock_transfer','sender_id','receiver_id')->withPivot('uid', 'cost','price','stock','status','created_at','updated_at','accepted_by','accepted_at');
    }

    
    /**
     * Get the purchaseitems of the inventory.
     */
    public function receivestocks()
    {
        return $this->belongsToMany('App\Inventory','stock_transfer','receiver_id','sender_id')->withPivot('uid', 'cost','price','stock','status','created_at','updated_at');
    }
}
