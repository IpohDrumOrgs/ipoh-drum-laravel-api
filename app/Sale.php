<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** @OA\Schema(
 *     title="Sale"
 * )
 */
class Sale extends Model
{
    /**
    * @OA\Property(property="id", type="integer")
    * @OA\Property(property="account_id", type="integer")
    * @OA\Property(property="user_id", type="integer")
    * @OA\Property(property="uid", type="string")
    * @OA\Property(property="sono", type="string")
    * @OA\Property(property="totalqty", type="integer")
    * @OA\Property(property="discpctg", type="double")
    * @OA\Property(property="totalcost", type="double")
    * @OA\Property(property="totalbfdisc", type="double")
    * @OA\Property(property="totalbftax", type="double")
    * @OA\Property(property="totaldisc", type="float")
    * @OA\Property(property="grandtotal", type="double")
    * @OA\Property(property="payment", type="double")
    * @OA\Property(property="outstanding", type="double")
    * @OA\Property(property="status", type="string")
    * @OA\Property(property="lastedit_by", type="string")
    * @OA\Property(property="remark", type="string")
    * @OA\Property(property="docdate", type="string")
    * @OA\Property(property="pos", type="int")
    * @OA\Property(property="remember_token", type="string")
    * @OA\Property(property="created_at", type="string")
    * @OA\Property(property="updated_at", type="string")
    */
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
     * Get the creator of the purchase.
     */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

}
