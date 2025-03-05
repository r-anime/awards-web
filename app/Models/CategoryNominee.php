<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Entry;

class CategoryNominee extends Model
{
    // use HasFactory;

    public function category(){
        return $this->belongsTo(Category::class);
    }
    
    public function entry(){
        return $this->belongsTo(Entry::class);
    }
}
