<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class CategoryInfo extends Model
{
    // use HasFactory;
    public $timestamps = false;

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
