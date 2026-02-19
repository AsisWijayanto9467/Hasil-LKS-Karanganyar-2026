<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Administrator extends Model
{
    use HasApiTokens;
    
    protected $table = "administrators";

    protected $fillable = [
        "username",
        "password",
        "last_login_at"
    ];

    protected $hidden = [
        "password"
    ];

    protected $casts = [
        "last_login_at" => "datetime"
    ];
}
