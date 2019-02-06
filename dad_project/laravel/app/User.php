<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;
    const IS_NOT_ADMIN = 0;
    const IS_ADMIN = 1;

    const IS_NOT_BLOCKED = 0;
    const IS_BLOCKED = 1;
    /**
     * DB Table name
     */
    protected $table = 'users';
    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nickname',
        //'admin',
        'blocked',
        'reason_blocked',
        'reason_reactivated',
        'created_at',
        'updated_at'
    ];


    /**
     * Use fillable or guarded
     */
//    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * DB table mapping - The games that belong to the user.
     */
    public function games()
    {
        return $this->belongsToMany('App\Game',
                                    'game_user',
                                    'user_id',
                                    'game_id');
    }


    /**
     * Helper functions
     */
    public function isAdmin()
    {
        return $this->admin == User::IS_ADMIN;
    }

    public function isBlocked()
    {
        return $this->blocked == User::IS_BLOCKED;
    }
}
