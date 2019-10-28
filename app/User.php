<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * @SWG\Definition(title="User")
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens;


    /**
     * Use username to login user.
     */
    // public function findForPassport($username)
    // {
    //     return $this->where('uname', $username)->first();
    // }
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uname', 'uid', 'email', 'name', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
        'status' => 'boolean',
        'last_login' => 'datetime',
        'last_active' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function linkedSocialAccounts()
    {
        return $this->hasMany(LinkedSocialAccount::class);
    }

    /**
     * Get the role belongs to the users.
     */
    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    /**
     * Get the group belongs to user.
     */
    public function groups()
    {
        return $this->belongsToMany('App\Group')->withPivot('desc', 'status', 'lastedit_by', 'created_at', 'updated_at');
    }

    /**
     * Get the logs belongs to the user.
     */
    public function logs()
    {
        return $this->hasMany('App\Log');
    }

    /**
     * Get the created payments of the user.
     */
    public function payments()
    {
        return $this->hasMany('App\Payment');
    }

    /**
     * Get the created sales of the user.
     */
    public function sales()
    {
        return $this->hasMany('App\Sale', 'user_id');
    }

    /**
     * Get the created purchases of the user.
     */
    public function purchases()
    {
        return $this->hasMany('App\Purchase', 'user_id');
    }
}
