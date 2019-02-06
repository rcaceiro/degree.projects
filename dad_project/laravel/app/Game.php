<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    const TYPE_SINGLEPLAYER = 'singleplayer';
    const TYPE_MULTIPLAYER = 'multiplayer';

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_CANCELED = 'canceled';
    /**
     * DB Table name
     */
    protected $table = 'games';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'status',
        'winner',
        'total_players',
        'created_by',
        'created_at',
        'updated_at'
    ];


    /**
     * Use fillable or guarded
     */
//    protected $guarded = [];

    /**
     * DB table mapping - The users that belong to the game.
     */
    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
