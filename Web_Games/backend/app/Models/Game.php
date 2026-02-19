<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = "games";

    protected $fillable = [
        "title",
        "slug",
        "description",
        "created_by"
    ];

    public function user() {
        return $this->belongsTo(User::class, "created_by");
    }

    public function gameVersion() {
        return $this->hasMany(GameVersion::class);
    }
}
