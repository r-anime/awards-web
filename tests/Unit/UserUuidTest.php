<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserUuidTest extends TestCase
{
    public function test_uuid_is_the_only_generated_unique_id(): void
    {
        $this->assertSame(['uuid'], (new User)->uniqueIds());
    }

    public function test_id_remains_database_generated(): void
    {
        $user = new User;

        $this->assertTrue($user->getIncrementing());
        $this->assertSame('int', $user->getKeyType());
    }

    public function test_a_uuid_is_generated_before_insert(): void
    {
        $user = new User;
        $user->setUniqueIds();

        $this->assertTrue(Str::isUuid($user->uuid));
    }

    public function test_an_explicit_uuid_is_not_overwritten(): void
    {
        $existing = (string) Str::uuid();

        $user = new User(['uuid' => $existing]);
        $user->setUniqueIds();

        $this->assertSame($existing, $user->uuid);
    }
}
