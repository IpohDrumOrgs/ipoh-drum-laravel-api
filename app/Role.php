<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{  
    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
   protected $casts = [
       'status' => 'boolean',
       'created_at' => 'datetime',
       'updated_at' => 'datetime',
   ];
   /**
    * Get the modules for the roles.
    */
   public function modules()
   {
       return $this->belongsToMany('App\Module')->withPivot( 'clearance');
   }

   /**
    * Get the users for the role.
    */
   public function users()
   {
       return $this->hasMany('App\User');
   }
}
