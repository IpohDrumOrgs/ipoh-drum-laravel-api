<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/** @OA\Schema(
 *     title="User"
 * )
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /** @OA\Property(property="id", type="integer"),
     * @OA\Property(property="role_id", type="integer"),
     * @OA\Property(property="uid", type="integer"),
     * @OA\Property(property="name", type="string"),
     * @OA\Property(property="email", type="string"),
     * @OA\Property(property="icno", type="string"),
     * @OA\Property(property="tel1", type="string"),
     * @OA\Property(property="tel2", type="string"),
     * @OA\Property(property="address1", type="string"),
     * @OA\Property(property="address2", type="string"),
     * @OA\Property(property="postcode", type="string"),
     * @OA\Property(property="city", type="string"),
     * @OA\Property(property="state", type="string"),
     * @OA\Property(property="country", type="string"),
     * @OA\Property(property="password", type="string"),
     * @OA\Property(property="status", type="string"),
     * @OA\Property(property="last_login", type="string"),
     * @OA\Property(property="last_active", type="string"),
     * @OA\Property(property="lastedit_by", type="string"),
     * @OA\Property(property="remember_token", type="string"),
     * @OA\Property(property="created_at", type="string"),
     * @OA\Property(property="updated_at", type="string")
     */

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
        'uid', 'email', 'name', 'password'
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
