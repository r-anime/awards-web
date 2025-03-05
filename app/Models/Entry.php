<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryInfo;
use App\Models\CategoryEligible;
use App\Models\CategoryNominee;
use App\Models\FinalVote;
use App\Models\NomineeVote;
use App\Models\Result;

class Entry extends Model
{
    // Returns Anime/Character/VoiceActor/Theme
    public function item() {
        return $this->morphTo();
    }

    public function eligibles() {
        return $this->hasMany(CategoryEligible::class, 'entry_id', 'id');
    }

    public function nominees() {
        return $this->hasMany(CategoryNominee::class, 'entry_id', 'id');
    }

    public function nomineevotes() {
        return $this->hasMany(NomineeVote::class, 'entry_id', 'id');
    }

    public function finalvotes() {
        return $this->hasMany(FinalVote::class, 'entry_id', 'id');
    }

    public function results() {
        return $this->hasMany(Result::class, 'entry_id', 'id');
    }
}
