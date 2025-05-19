<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Entry;

class WatchStat extends Model
{
    // use HasFactory;

    public function anime(){
        return $this->belongsTo(Entry::class);
    }
}
