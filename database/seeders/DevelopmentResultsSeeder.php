<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryInfo;
use App\Models\Entry;
use App\Models\Result;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DevelopmentResultsSeeder extends Seeder
{
    private const YEAR = 2024;

    public function run(): void
    {
        $archive = json_decode(
            file_get_contents(app_path('Console/Commands/archive/results'.self::YEAR.'.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        foreach ($archive['sections'] as $section) {
            foreach ($section['awards'] as $order => $award) {
                $category = Category::query()->updateOrCreate(
                    [
                        'year' => self::YEAR,
                        'name' => $award['name'],
                    ],
                    [
                        'type' => $section['slug'],
                        'entry_type' => $award['entryType'],
                        'order' => $order + 1,
                    ],
                );

                CategoryInfo::query()->updateOrCreate(
                    ['category_id' => $category->id],
                    [
                        'description' => $award['blurb'] ?? '',
                        'sotc_blurb' => $section['blurb'] ?? '',
                    ],
                );

                foreach ($award['nominees'] ?? [] as $nominee) {
                    $entry = $this->entry($archive, $award['entryType'], $nominee);

                    Result::query()->updateOrCreate(
                        [
                            'year' => self::YEAR,
                            'category_id' => $category->id,
                            'entry_id' => $entry->id,
                        ],
                        [
                            'name' => $this->name($archive, $award['entryType'], $nominee),
                            'image' => $nominee['altimg'] ?: '/images/awards2024.png',
                            'jury_rank' => $nominee['jury'],
                            'public_rank' => $nominee['public'],
                            'description' => $nominee['writeup'] ?? '',
                            'staff_credits' => $this->staffCredits($nominee['staff'] ?? ''),
                        ],
                    );
                }
            }
        }

        Cache::forget('results_yearlist');
        Cache::forget('results_'.self::YEAR);
    }

    private function entry(array $archive, string $entryType, array $nominee): Entry
    {
        return Entry::query()->updateOrCreate(
            [
                'anilist_id' => $nominee['id'],
                'type' => $this->entryType($entryType),
                'year' => self::YEAR,
            ],
            [
                'name' => $this->name($archive, $entryType, $nominee),
                'image' => $nominee['altimg'] ?: '/images/awards2024.png',
            ],
        );
    }

    private function name(array $archive, string $entryType, array $nominee): string
    {
        if (! empty($nominee['altname'])) {
            return $nominee['altname'];
        }

        $id = (string) $nominee['id'];

        return match ($entryType) {
            'shows' => $archive['anime'][$id],
            'characters' => $archive['characters'][$id]['name'],
            'vas' => $archive['characters'][$id]['va'],
            'themes' => $archive['themes'][$id],
        };
    }

    private function entryType(string $entryType): string
    {
        return match ($entryType) {
            'shows' => 'anime',
            'characters' => 'char',
            'vas' => 'va',
            'themes' => 'theme',
        };
    }

    private function staffCredits(string $staff): ?array
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $staff))));
        if ($lines === []) {
            return null;
        }

        return array_map(function (string $line): array {
            if (str_contains($line, ':')) {
                [$role, $name] = array_map('trim', explode(':', $line, 2));

                return compact('role', 'name');
            }

            return ['role' => 'Studio', 'name' => $line];
        }, $lines);
    }
}
