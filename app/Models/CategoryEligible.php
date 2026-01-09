<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Entry;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendAuditWebhook;
use App\Models\Option;


class CategoryEligible extends Model
{
    // use HasFactory;

    public function category(){
        return $this->belongsTo(Category::class);
    }
    
    public function entry(){
        return $this->belongsTo(Entry::class);
    }

    protected static function booted(): void
    {
        static::created(function (CategoryEligible $eligible): void {
            self::sendAuditEvent('entry_added_to_category', $eligible);
        });

        static::updated(function (CategoryEligible $eligible): void {
            // Only audit if active status changed
            if ($eligible->wasChanged('active')) {
                self::sendAuditEvent('entry_category_status_changed', $eligible);
            }
        });

        static::deleting(function (CategoryEligible $eligible): void {
            // Load relationships before deletion
            if (!$eligible->relationLoaded('entry')) {
                $eligible->load('entry');
            }
            if (!$eligible->relationLoaded('category')) {
                $eligible->load('category');
            }
        });

        static::deleted(function (CategoryEligible $eligible): void {
            self::sendAuditEvent('entry_removed_from_category', $eligible);
        });
    }

    private static function sendAuditEvent(string $eventType, CategoryEligible $eligible): void
    {
        $webhookUrl = Option::get('audit_channel_webhook');
        if (!$webhookUrl) {
            return;
        }

        $actor = Auth::user();

        $action = match ($eventType) {
            'entry_removed_from_category' => 'removed entry from category',
            'entry_category_status_changed' => 'changed entry status in category',
            default => 'added entry to category',
        };

        $actorStr = $actor
            ? sprintf('#%d %s (%s)', $actor->id, $actor->name ?? $actor->reddit_user ?? 'Unknown', $actor->reddit_user ?? 'n/a')
            : 'System';

        // Load relationships if not already loaded
        if (!$eligible->relationLoaded('entry')) {
            try {
                $eligible->load('entry');
            } catch (\Exception $e) {
                // Relationship might not be accessible, fetch directly
                $eligible->setRelation('entry', Entry::find($eligible->entry_id));
            }
        }
        if (!$eligible->relationLoaded('category')) {
            try {
                $eligible->load('category');
            } catch (\Exception $e) {
                // Relationship might not be accessible, fetch directly
                $eligible->setRelation('category', Category::find($eligible->category_id));
            }
        }

        $entryStr = $eligible->entry 
            ? sprintf('#%d %s (%s, %d)', $eligible->entry->id, $eligible->entry->name, $eligible->entry->type, $eligible->entry->year)
            : sprintf('Entry #%d', $eligible->entry_id);

        $categoryStr = $eligible->category
            ? sprintf('#%d %s (%d)', $eligible->category->id, $eligible->category->name ?? 'Unknown', $eligible->category->year ?? 'N/A')
            : sprintf('Category #%d', $eligible->category_id);

        $embed = [
            'title' => 'Category Entry audit',
            'description' => $actorStr . ' (' . $action . ')',
            'color' => $eventType === 'entry_removed_from_category' ? 0xff0000 : ($eventType === 'entry_category_status_changed' ? 0xffa500 : 0x5865f2),
            'timestamp' => now()->toIso8601String(),
            'fields' => [
                [
                    'name' => 'Entry',
                    'value' => $entryStr,
                    'inline' => true,
                ],
                [
                    'name' => 'Category',
                    'value' => $categoryStr,
                    'inline' => true,
                ],
            ],
        ];

        if ($eventType === 'entry_category_status_changed') {
            $oldActive = $eligible->getOriginal('active');
            $newActive = $eligible->active;
            $embed['fields'][] = [
                'name' => 'Active Status',
                'value' => sprintf('From: %s → To: %s', $oldActive ? 'Active' : 'Inactive', $newActive ? 'Active' : 'Inactive'),
                'inline' => false,
            ];
        } elseif ($eventType === 'entry_added_to_category') {
            $embed['fields'][] = [
                'name' => 'Active',
                'value' => $eligible->active ? 'Yes' : 'No',
                'inline' => true,
            ];
        }

        try {
            // Send webhook immediately
            SendAuditWebhook::dispatchSync($webhookUrl, ['embeds' => [$embed]]);
        } catch (\Throwable $e) {
            // Intentionally ignore webhook errors to avoid impacting UX
        }
    }
}
