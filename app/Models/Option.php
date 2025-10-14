<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    
    protected $fillable = [
        'option',
        'value',
    ];

    /**
     * Get an option value by key
     */
    public static function get(string $key, $default = null)
    {
        $option = static::where('option', $key)->first();
        return $option ? $option->value : $default;
    }

    /**
     * Set an option value
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['option' => $key],
            ['value' => $value]
        );
    }
}
