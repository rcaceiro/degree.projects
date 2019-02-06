<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    const FACE_TILE = "tile";
    const FACE_HIDDEN = "hidden";

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;

    /**
     * DB Table name
     */
    protected $table = 'images';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'face',
        'active',
        'path'
    ];


    /**
     * Use fillable or guarded
     */
//    protected $guarded = [];

    public function isActive()
    {
        return $this->active == Image::IS_ACTIVE;
    }

    public function isFaceTile()
    {
        return $this->face == Image::FACE_TILE;
    }

    public function isFaceHidden()
    {
        return $this->face == Image::FACE_HIDDEN;
    }

    public function setFaceTile()
    {
        return $this->face = Image::FACE_TILE;
    }

    public function setFaceHidden()
    {
        return $this->face = Image::FACE_HIDDEN;
    }
}
