<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Entry;

class NomineeVote extends Model
{
    public function entry(){
        return $this->belongsTo(Entry::class);
    }
    
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
