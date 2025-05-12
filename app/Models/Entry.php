<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    public function category_eligibles()
    {
        return $this->belongsToMany(CategoryEligible::class);
    }
    
    public function parent(){
        return $this->hasOne(Entry::class, 'id', 'parent_id');
    }
}
