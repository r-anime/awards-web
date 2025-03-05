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
use App\Models\WatchStatCalculated;

class Category extends Model
{
    // use HasFactory;
    public $timestamps = false;

    public function info(){
        return $this->hasOne(CategoryInfo::class);
    }

    // Everything Eligible To Be Nominated
    public function eligibles(){
        return $this->hasMany(CategoryEligible::class);
    }

    // Final Nominees
    public function nominees(){
        return $this->hasMany(CategoryNominee::class);
    }

    public function nomineevotes(){
        return $this->hasMany(NomineeVote::class);
    }

    public function finalvotes(){
        return $this->hasMany(FinalVote::class);
    }

    public function results(){
        return $this->hasMany(Result::class);
    }

    public function watchstatscalculated(){
        return $this->hasMany(WatchStatCalculated::class);
    }
}
