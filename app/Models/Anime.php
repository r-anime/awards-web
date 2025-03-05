<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Character;
use App\Models\Entry;
use App\Models\Theme;
use App\Models\WatchStat;
use App\Models\WatchStatCalculated;

class Anime extends Model
{
    // use HasFactory;
    protected $table = 'anime';

    // the Entries model is used to link to most other models as they can take Anime/Character/VoiceActor/Theme models
    public function entry() {
        return $this->morphMany(Entry::class, 'item');
    }

    public function characters() {
        return $this->hasMany(Character::class);
    }

    public function themes() {
        return $this->hasMany(Theme::class);
    }

    // watch stats
    public function watchstats() {
        return $this->hasMany(WatchStat::class);
    }
    public function watchstatscalculated() {
        return $this->hasMany(WatchStatCalculated::class);
    }
    public function watched(){
        return $this->watchstats()->count();
    }
}
