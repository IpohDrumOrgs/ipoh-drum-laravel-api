<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(title="Sale")
 */
class Sale extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'totalqty' => 'integer',
        'discpctg' => 'float',
        'totaldisc' => 'float',
        'totalbfdisc' => 'float',
        'totalbftax' => 'float',
        'totaldisc' => 'float',
        'grandtotal' => 'float',
        'payment' => 'float',
        'outstanding' => 'float',
        'docdate' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
   /**
     * Get the payments of the sale.
     */
    public function payments()
    {
        return $this->belongsToMany('App\Payment','payment_sale')->withPivot( 'amt','discount','type','status','created_at','updated_at');
    }

    /**
     * Get the sale items of the sale.
     */
    public function saleitems()
    {
        return $this->hasMany('App\SaleItem');
    }

    /**
     * Get the creator of the purchase.
     */
    public function creator()
    {
        return $this->belongsTo('App\User','user_id');
    }

    /**
     * Get the account of the purchase.
     */
    public function account()
    {
        return $this->belongsTo('App\Account');
    }
}
