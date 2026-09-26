<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationAnswerLimitTest extends TestCase
{
    use RefreshDatabase;

    private const LIMIT = 200;

    private function application(array $overrides = []): Application
    {
        return Application::create([
            'year' => 2026,
            'start_time' => now()->subDay(),
            'end_time' => now()->addDay(),
            'form' => [array_merge([
                'id' => 'q-essay',
                'type' => 'essay',
                'question' => 'Why do you want to help?',
                'character_limit' => self::LIMIT,
            ], $overrides)],
        ]);
    }

    public function test_an_answer_at_the_limit_is_accepted(): void
    {
        $this->application();

        $this->actingAs(User::factory()->create())
            ->post('/participate/application/submit', [
                'question_q-essay' => str_repeat('a', self::LIMIT),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('app_answers', ['question_id' => 'q-essay']);
    }

    public function test_an_answer_over_the_limit_is_rejected_on_that_field(): void
    {
        $this->application();

        $this->actingAs(User::factory()->create())
            ->post('/participate/application/submit', [
                'question_q-essay' => str_repeat('a', self::LIMIT + 1),
            ])
            ->assertSessionHasErrors('question_q-essay');

        $this->assertDatabaseMissing('app_answers', ['question_id' => 'q-essay']);
    }

    public function test_multibyte_answers_are_counted_the_way_the_browser_counts_them(): void
    {
        $this->application();

        // The browser caps on JS string length (UTF-16 units); the server counts
        // code points. Astral characters cost 2 in the browser and 1 here, so the
        // server must never be the stricter of the two.
        $this->actingAs(User::factory()->create())
            ->post('/participate/application/submit', [
                'question_q-essay' => str_repeat('😀', self::LIMIT),
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_questions_without_an_explicit_limit_fall_back_to_5000(): void
    {
        $this->application(['character_limit' => null]);

        $this->actingAs(User::factory()->create())
            ->post('/participate/application/submit', [
                'question_q-essay' => str_repeat('a', 5001),
            ])
            ->assertSessionHasErrors('question_q-essay');
    }
}
