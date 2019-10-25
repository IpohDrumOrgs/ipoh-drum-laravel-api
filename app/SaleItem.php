<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
     /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'qty' => 'integer',
        'price' => 'float',
        'totaldisc' => 'float',
        'linetotal' => 'float',
        'payment' => 'float',
        'outstanding' => 'float',
        'docdate' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
     /**
     * Get the sale of the sale item.
     */
    public function sale()
    {
        return $this->belongsTo('App\Sale');
    }

    
    /**
     * Get the inventory of the purchase item.
     */
    public function inventory()
    {
        return $this->belongsTo('App\Inventory');
    }
    
    /**
     * Get the inventory of the purchase item.
     */
    public function inventorybatch()
    {
        return $this->belongsTo('App\InventoryBatch','inventory_batch_id');
    }
     /**
     * Get the sale of the sale item.
     */
    public function batches()
    {
        return $this->belongsToMany('App\Batch','batch_sale_item')->withPivot( 'stock','status','created_at','updated_at');
    }
}
