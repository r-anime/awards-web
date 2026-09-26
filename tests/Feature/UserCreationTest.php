<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_created_user_receives_a_uuid(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Str::isUuid($user->uuid));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'uuid' => $user->uuid]);
    }

    public function test_id_is_still_assigned_by_the_database(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->assertIsInt($first->id);
        $this->assertSame($first->id + 1, $second->id);
    }
}
