<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    /**
    * 
    */
    public function vouchers()
    {
        return $this->hasMany('App\Voucher');
    }
    
    /**
    * 
    */
    public function storereviews()
    {
        return $this->hasMany('App\StoreReview');
    }
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
      /**
     *
     */
    public function promotions()
    {
        return $this->hasMany('App\ProductPromotion');
    }

    /**
     *
     */
    public function warranties()
    {
        return $this->hasMany('App\Warranty');
    }
    
    /**
     *
     */
    public function shippings()
    {
        return $this->hasMany('App\Shipping');
    }

}
