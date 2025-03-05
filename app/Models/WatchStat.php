<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Anime;

class WatchStat extends Model
{
    // use HasFactory;

    public function anime(){
        return $this->belongsTo(Anime::class);
    }
}
