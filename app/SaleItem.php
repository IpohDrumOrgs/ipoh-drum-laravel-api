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
     * Get the sale of the sale item.
     */
    public function inventory()
    {
        return $this->belongsTo('App\Inventory');
    }
    
    /**
     * Get the inventory of the purchase item.
     */
    public function inventoryfamily()
    {
        return $this->belongsTo('App\InventoryFamily', 'inventory_family_id');
    }
    /**
     * Get the inventory of the purchase item.
     */
    public function pattern()
    {
        return $this->belongsTo('App\Pattern');
    }
    
    /**
     * Get the inventory of the purchase item.
     */
    public function ticketfamily()
    {
        return $this->belongsTo('App\TicketFamily');
    }
    

}
