<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'message',
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if IP has exceeded weekly limit (5 submissions)
     */
    public static function hasExceededWeeklyLimit(string $ipAddress): bool
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $submissionCount = self::where('ip_address', $ipAddress)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();
            
        return $submissionCount >= 5;
    }
}
