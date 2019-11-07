<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
     /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'hasbranch' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the companytype for the company.
     */
    public function companytype()
    {
        return $this->belongsTo('App\CompanyType', 'company_type_id');
    }

    /**
     * Get the groups for the company type.
     */
    public function groups()
    {
        return $this->hasMany('App\Group');
    }

    /**
     * Get the branches of the company.
     */
    public function branches()
    {
        return $this->hasMany('App\Company', 'company_id');
    }

    /**
     * Get the parent company of the company.
     */
    public function mothercompany()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }

    /**
     * Get the inventories of the company.
     */
    public function inventories()
    {
        return $this->hasMany('App\Inventory');
    }

    /**
     * Get the account of the company.
     */
    public function account()
    {
        return $this->hasOne('App\Account');
    }

     /**
     * company roles
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role','company_role_user')->withPivot('user_id','role_id','company_id','assigned_by','assigned_at', 'unassigned_by', 'unassigned_at','remark','status');
    }

    /**
     * company users
     */
    public function users()
    {
        return $this->belongsToMany('App\User','company_role_user')->withPivot('user_id','role_id','company_id','assigned_by','assigned_at', 'unassigned_by', 'unassigned_at','remark','status');
    }
    
    /**
     * Get the inventories of the company.
     */
    public function stores()
    {
        return $this->hasMany('App\Store');
    }
}
