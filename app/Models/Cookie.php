<?php

namespace App\Models;

use App\Models\GameUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cookie extends Model
{
    use HasFactory;

    protected $fillable = [
        'cookies',
    ];

    public function gameUser()
    {
        return $this->belongsTo(GameUser::class);
    }
}
