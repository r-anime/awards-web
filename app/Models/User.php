<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Panel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'reddit_user',
        'role',
        'flags',
        'avatar',
        'about',
        'uuid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Allow all logged-in users to access the panel, including role -1 users
        return true;
    }

    public function getFilamentName(): string
    {
        return $this->name ?? $this->reddit_user ?? 'Unknown User';
    }

    public function appAnswers()
    {
        return $this->hasMany(AppAnswer::class, 'applicant_id');
    }

    public function appScores()
    {
        return $this->hasMany(AppScore::class, 'applicant_id');
    }

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            self::sendAuditEvent('user_created', $user, [
                'role' => (int) $user->role,
            ]);
        });

        static::updated(function (User $user): void {
            if ($user->wasChanged('role')) {
                self::sendAuditEvent('user_role_changed', $user, [
                    'old_role' => (int) $user->getOriginal('role'),
                    'new_role' => (int) $user->role,
                ]);
            }
        });

        static::deleted(function (User $user): void {
            self::sendAuditEvent('user_deleted', $user, [
                'role' => (int) ($user->role ?? 0),
            ]);
        });
    }

    private static function sendAuditEvent(string $eventType, User $subjectUser, array $details = []): void
    {
        $webhookUrl = Option::get('audit_webhook_url');
        if (!$webhookUrl) {
            return;
        }

        $actor = Auth::user();

        $payload = [
            'event' => $eventType,
            'timestamp' => now()->toIso8601String(),
            'subject' => [
                'id' => $subjectUser->id,
                'uuid' => $subjectUser->uuid ?? null,
                'name' => $subjectUser->name,
                'reddit_user' => $subjectUser->reddit_user,
            ],
            'actor' => $actor ? [
                'id' => $actor->id,
                'name' => $actor->name,
                'reddit_user' => $actor->reddit_user,
            ] : null,
            'details' => $details,
        ];

        try {
            Http::timeout(5)->acceptJson()->asJson()->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            // Intentionally ignore webhook errors to avoid impacting UX
        }
    }
}
