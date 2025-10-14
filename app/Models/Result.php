<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'category_id',
        'name',
        'image',
        'entry_id',
        'jury_rank',
        'public_rank',
        'description',
        'staff_credits',
    ];

    protected $casts = [
        'staff_credits' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }
}
