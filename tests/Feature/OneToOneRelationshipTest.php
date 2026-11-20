<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OneToOneRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_one_profile(): void
    {
        $user = User::factory()->create();

        $profile = $user->profile()->create([
            'phone_number' => '+63 912 345 6789',
            'bio' => 'Software developer',
        ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'user_id' => $user->id,
            'phone_number' => '+63 912 345 6789',
        ]);

        $this->assertEquals($profile->id, $user->profile->id);
    }

    public function test_profile_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'phone_number' => '+63 999 888 7777',
            'bio' => 'Inverse relation test',
        ]);

        $this->assertEquals($user->id, $profile->user->id);
        $this->assertEquals($user->email, $profile->user->email);
    }

    public function test_deleting_user_cascades_and_deletes_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([
            'phone_number' => '12345',
            'bio' => 'To be deleted',
        ]);

        $this->assertDatabaseCount('profiles', 1);

        $user->delete();

        $this->assertDatabaseCount('profiles', 0);
    }
}
