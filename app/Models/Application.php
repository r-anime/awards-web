<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AppQuestion;

class Application extends Model
{
    // use HasFactory;

    public function questions(){
        return $this->hasMany(AppQuestion::class);
    }
}
