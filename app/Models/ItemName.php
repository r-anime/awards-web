<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemName extends Model
{
    protected $table = 'item_names';
    public $timestamps = false;

    // use HasFactory;

    public function entry(){
        return $this->belongsTo(Entry::class);
    }
}
