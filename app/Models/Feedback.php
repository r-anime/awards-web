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
        'ip_hash',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Generate IP hash for rate limiting
     */
    public static function generateIpHash(string $ipAddress): string
    {
        return hash('sha256', $ipAddress . config('app.key'));
    }

    /**
     * Check if IP has exceeded weekly limit (5 submissions)
     */
    public static function hasExceededWeeklyLimit(string $ipAddress): bool
    {
        $ipHash = self::generateIpHash($ipAddress);
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $submissionCount = self::where('ip_hash', $ipHash)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();
            
        return $submissionCount >= 5;
    }
}
