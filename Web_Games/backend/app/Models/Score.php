<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $table = "scores";

    protected $fillable = [
        "user_id",
        "game_version_id",
        "score"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function gameVersion() {
        return $this->belongsTo(GameVersion::class);
    }
}
