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
use App\Jobs\SendAuditWebhook;
use App\Models\Option;

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
        /*
        static::created(function (User $user): void {
            self::sendAuditEvent('user_created', $user, [
                'role' => (int) $user->role,
            ]);
        });*/

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
        $webhookUrl = Option::get('audit_channel_webhook');
        if (!$webhookUrl) {
            return;
        }

        $actor = Auth::user();

        $action = match ($eventType) {
            'user_deleted' => 'deleted',
            'user_role_changed' => 'edited',
            default => 'created',
        };

        $actorStr = $actor
            ? sprintf('#%d %s (%s)', $actor->id, $actor->name ?? $actor->reddit_user ?? 'Unknown', $actor->reddit_user ?? 'n/a')
            : 'System';

        $subjectStr = sprintf('#%d %s (%s)', $subjectUser->id, $subjectUser->name ?? $subjectUser->reddit_user ?? 'Unknown', $subjectUser->reddit_user ?? 'n/a');

        $embed = [
            'title' => 'User audit',
            'description' => $actorStr . ' (' . $action . ') ' . $subjectStr,
            'color' => $eventType === 'user_deleted' ? 0xff0000 : ($eventType === 'user_role_changed' ? 0xffa500 : 0x5865f2),
            'timestamp' => now()->toIso8601String(),
        ];

        if ($eventType === 'user_role_changed') {
            $embed['fields'] = [
                [
                    'name' => 'Old role',
                    'value' => (string) ($details['old_role'] ?? ''),
                    'inline' => true,
                ],
                [
                    'name' => 'New role',
                    'value' => (string) ($details['new_role'] ?? ''),
                    'inline' => true,
                ],
            ];
        }

        try {
            // Queue the webhook to avoid impacting request lifecycle
            SendAuditWebhook::dispatch($webhookUrl, ['embeds' => [$embed]])->onQueue('default');
        } catch (\Throwable $e) {
            // Intentionally ignore webhook errors to avoid impacting UX
        }
    }
}
