<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * Get several option values in one query. Takes [key => fallback] and returns [key => value],
     * using the fallback for options that have never been saved, like get()'s $default
     *
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public static function getMany(array $defaults): array
    {
        return array_merge(
            $defaults,
            static::whereIn('option', array_keys($defaults))->pluck('value', 'option')->all(),
        );
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

    /**
     * Set several option values through set(), so model events still fire. All or nothing:
     * if one fails, none are saved
     *
     * @param  array<string, mixed>  $values  key => value
     */
    public static function setMany(array $values): void
    {
        if ($values === []) {
            return;
        }

        DB::transaction(function () use ($values) {
            foreach ($values as $key => $value) {
                static::set($key, $value);
            }
        });
    }
}
