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
     * @OA\Property(property="discpctg", type="number")
     * @OA\Property(property="totalcost", type="number")
     * @OA\Property(property="totalbfdisc", type="number")
     * @OA\Property(property="totalbftax", type="number")
     * @OA\Property(property="totaldisc", type="number")
     * @OA\Property(property="grandtotal", type="number")
     * @OA\Property(property="payment", type="number")
     * @OA\Property(property="outstanding", type="number")
     * @OA\Property(property="status", type="string")
     * @OA\Property(property="lastedit_by", type="string")
     * @OA\Property(property="remark", type="string")
     * @OA\Property(property="docdate", type="string")
     * @OA\Property(property="pos", type="integer")
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
        return $this->hasMany('App\Payment');
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
    public function user()
    {
        return $this->belongsTo('App\User');
    }


    /**
     * Get the creator of the purchase.
     */
    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
