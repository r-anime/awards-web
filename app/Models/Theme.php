<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Anime;
use App\Models\Entry;

class Theme extends Model
{
    // use HasFactory;
    public function entry() {
        return $this->morphMany(Entry::class, 'item');
    }

    public function anime(){
        return $this->belongsTo(Anime::class);
    }
}
