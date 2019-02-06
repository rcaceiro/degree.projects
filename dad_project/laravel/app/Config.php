<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    /**
     * DB Table name
     */
    protected $table = 'config';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'platform_email',
        'platform_email_properties',
        'img_base_path',
        'created_at',
        'updated_at'
    ];


    /**
     * Use fillable or guarded
     */
//    protected $guarded = [];

}
