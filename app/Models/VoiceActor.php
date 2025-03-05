<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Entry;

class VoiceActor extends Model
{
    // use HasFactory;
    public function entry() {
        return $this->morphMany(Entry::class, 'item');
    }
}
