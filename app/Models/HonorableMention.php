<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HonorableMention extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'category_id',
        'writeup',
    ];//

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
