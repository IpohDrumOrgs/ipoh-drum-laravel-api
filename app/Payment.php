<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
      /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'amt' => 'float',
        'discount' => 'float',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
   
    /**
     * Get the creator of the payment.
     */
    public function creator()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the account of the payment.
     */
    public function account()
    {
        return $this->belongsTo('App\Account');
    }

    /**
     * Get the sales of the payment.
     */
    public function sales()
    {
        return $this->belongsToMany('App\Sale','payment_sale')->withPivot( 'amt','discount','type','status','created_at','updated_at');
    }

    
    /**
     * Get the purchases of the payment.
     */
    public function purchases()
    {
        return $this->belongsToMany('App\Purchase','payment_purchase')->withPivot( 'amt','discount','type','status','created_at','updated_at');
    }
}
