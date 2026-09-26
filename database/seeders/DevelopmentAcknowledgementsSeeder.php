<?php

namespace Database\Seeders;

use App\Models\Acknowledgement;
use Illuminate\Database\Seeder;

class DevelopmentAcknowledgementsSeeder extends Seeder
{
    public function run(): void
    {
        $archive = json_decode(
            file_get_contents(app_path('Console/Commands/archive/acknowledgements.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        foreach ($archive as $year => $acknowledgements) {
            foreach ($acknowledgements as $acknowledgement) {
                Acknowledgement::query()->updateOrCreate(
                    [
                        'year' => $year,
                        'title' => $acknowledgement['title'],
                    ],
                    [
                        'subtitle' => '',
                        'content' => array_filter([
                            'english' => $acknowledgement['english'],
                            'japanese' => $acknowledgement['japanese'] ?? null,
                            'extra' => $acknowledgement['extra'] ?? null,
                        ], fn (?string $value): bool => $value !== null),
                        'order' => $acknowledgement['order'] ?? 5,
                    ],
                );
            }
        }
    }
}
