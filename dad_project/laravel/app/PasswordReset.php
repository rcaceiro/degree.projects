<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    /**
     * Needed to use Eloquent delete()
     */
    protected $primaryKey = 'email';
    /**
     * DB Table name
     */
    protected $table = 'password_resets';
    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];


    /**
     * Use fillable or guarded
     */
//    protected $guarded = [];

    /**
     * Override to prevent CRUD operations with updated_at column
     * @param $value
     */
    public function setUpdatedAtAttribute($value)
    {
        // to Disable updated_at
    }
}
