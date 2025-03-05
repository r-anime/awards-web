<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Application;
use App\Models\AppScore;
use App\Models\AppAnswer;

class AppQuestion extends Model
{
    // use HasFactory;
    public $timestamps = false;

    public function application(){
        return $this->belongsTo(Application::class, 'id', 'app_id');
    }

    public function answers(){
        return $this->hasMany(AppAnswer::class);
    }

    // While this is not "correct" design as this should belong to the AppAnswer model, this makes it a little easier to manage for iterating through for allocations and statistics
    public function scores(){
        return $this->hasMany(AppScore::class);
    }
}
