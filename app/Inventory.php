<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** @OA\Schema(
 *     title="Inventory"
 * )
 */
class Inventory extends Model
{
    /** @OA\Property(property="id", type="integer"),
     * @OA\Property(property="store_id", type="integer"),
     * @OA\Property(property="uid", type="string"),
     * @OA\Property(property="code", type="string"),
     * @OA\Property(property="sku", type="string"),
     * @OA\Property(property="name", type="string"),
     * @OA\Property(property="desc", type="string"),
     * @OA\Property(property="cost", type="number"),
     * @OA\Property(property="price", type="number"),
     * @OA\Property(property="disc", type="number"),
     * @OA\Property(property="discpctg", type="number"),
     * @OA\Property(property="promoprice", type="number"),
     * @OA\Property(property="promostartdate", type="string"),
     * @OA\Property(property="promoenddate", type="string"),
     * @OA\Property(property="stock", type="integer"),
     * @OA\Property(property="salesqty", type="integer"),
     * @OA\Property(property="warrantyperiod", type="integer"),
     * @OA\Property(property="stockthreshold", type="integer"),
     * @OA\Property(property="backorder", type="integer"),
     * @OA\Property(property="status", type="integer"),
     * @OA\Property(property="lastedit_by", type="string"),
     * @OA\Property(property="created_at", type="string"),
     * @OA\Property(property="updated_at", type="string")
     */

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


    /**
     *
     */
    public function images()
    {
        return $this->hasMany('App\InventoryImage');
    }
}
