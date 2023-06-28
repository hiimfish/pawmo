<?php

namespace App\Models;

use App\Models\Cookie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GameUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
    ];

    protected $casts = [
        'coin_info' => 'integer',
    ];

    public function cookie()
    {
        return $this->hasOne(Cookie::class);
    }
}
