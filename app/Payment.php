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
     * Get the sales of the payment.
     */
    public function sales()
    {
        return $this->belongsToMany('App\Sale','payment_sale')->withPivot( 'amt','discount','type','status','created_at','updated_at');
    }

   
}
