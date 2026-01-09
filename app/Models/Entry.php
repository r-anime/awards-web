<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendAuditWebhook;
use App\Models\Option;

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

    protected static function booted(): void
    {
        static::created(function (Entry $entry): void {
            self::sendAuditEvent('entry_created', $entry);
        });

        static::updated(function (Entry $entry): void {
            self::sendAuditEvent('entry_updated', $entry);
        });

        static::deleted(function (Entry $entry): void {
            self::sendAuditEvent('entry_deleted', $entry);
        });
    }

    private static function sendAuditEvent(string $eventType, Entry $entry): void
    {
        $webhookUrl = Option::get('audit_channel_webhook');
        if (!$webhookUrl) {
            return;
        }

        $actor = Auth::user();

        $action = match ($eventType) {
            'entry_deleted' => 'deleted',
            'entry_updated' => 'edited',
            default => 'created',
        };

        $actorStr = $actor
            ? sprintf('#%d %s (%s)', $actor->id, $actor->name ?? $actor->reddit_user ?? 'Unknown', $actor->reddit_user ?? 'n/a')
            : 'System';

        $entryStr = sprintf('#%d %s (%s, %d)', $entry->id, $entry->name, $entry->type, $entry->year);

        $embed = [
            'title' => 'Entry audit',
            'description' => $actorStr . ' (' . $action . ') ' . $entryStr,
            'color' => $eventType === 'entry_deleted' ? 0xff0000 : ($eventType === 'entry_updated' ? 0xffa500 : 0x5865f2),
            'timestamp' => now()->toIso8601String(),
            'fields' => [
                [
                    'name' => 'Entry ID',
                    'value' => (string) $entry->id,
                    'inline' => true,
                ],
                [
                    'name' => 'Name',
                    'value' => $entry->name,
                    'inline' => true,
                ],
                [
                    'name' => 'Type',
                    'value' => $entry->type,
                    'inline' => true,
                ],
                [
                    'name' => 'Year',
                    'value' => (string) $entry->year,
                    'inline' => true,
                ],
            ],
        ];

        if ($eventType === 'entry_updated') {
            $changedFields = [];
            foreach ($entry->getDirty() as $key => $value) {
                $oldValue = $entry->getOriginal($key);
                $changedFields[] = [
                    'name' => ucfirst(str_replace('_', ' ', $key)),
                    'value' => sprintf('From: %s → To: %s', $oldValue ?? 'null', $value ?? 'null'),
                    'inline' => false,
                ];
            }
            if (!empty($changedFields)) {
                $embed['fields'] = array_merge($embed['fields'], $changedFields);
            }
        }

        try {
            // Send webhook immediately
            SendAuditWebhook::dispatchSync($webhookUrl, ['embeds' => [$embed]]);
        } catch (\Throwable $e) {
            // Intentionally ignore webhook errors to avoid impacting UX
        }
    }
}
