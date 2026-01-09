<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = [
        'anilist_id',
        'type',
        'name',
        'year',
        'theme_version',
        'image',
        'link',
        'parent_id',
    ];

    public function category_eligibles()
    {
        return $this->hasMany(CategoryEligible::class);
    }
    
    public function parent(){
        return $this->hasOne(self::class, 'id', 'parent_id');
    }

    public function grandparents()
    {
        return $this->parent()->with('grandparents');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function grandchildren()
    {
        return $this->children()->with('grandchildren');
    }

    public function item_names()
    {
        return $this->hasMany(ItemName::class);
    }

    /* bad code doesn't work
    public function searchable_string()
    {
        return $this->name . ' ' . $this->item_names->pluck('name')->implode(' ');
    }
    */
}
