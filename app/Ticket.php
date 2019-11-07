<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    /**
     * Get the company of the inventory.
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
     * Get the saleitems of the inventory.
     */
    public function verificationcodes()
    {
        return $this->hasMany('App\VerificationCode');
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
}
