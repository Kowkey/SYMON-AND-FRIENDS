<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManyToManyRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_multiple_roles_and_vice_versa(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin']);
        $editorRole = Role::create(['name' => 'Editor']);

        $user->roles()->attach([$adminRole->id, $editorRole->id]);

        $this->assertDatabaseCount('role_user', 2);
        $this->assertCount(2, $user->roles);
        $this->assertTrue($user->roles->contains($adminRole));
        $this->assertTrue($user->roles->contains($editorRole));

        // Test inverse: Role has users
        $this->assertCount(1, $adminRole->users);
        $this->assertEquals($user->id, $adminRole->users->first()->id);
    }

    public function test_sync_replaces_assigned_roles(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin']);
        $editorRole = Role::create(['name' => 'Editor']);
        $subscriberRole = Role::create(['name' => 'Subscriber']);

        $user->roles()->attach([$adminRole->id, $editorRole->id]);
        $this->assertCount(2, $user->roles);

        // Sync to only subscriber
        $user->roles()->sync([$subscriberRole->id]);

        $user->refresh();
        $this->assertCount(1, $user->roles);
        $this->assertTrue($user->roles->contains($subscriberRole));
        $this->assertFalse($user->roles->contains($adminRole));
    }

    public function test_detach_removes_role(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin']);

        $user->roles()->attach($adminRole->id);
        $this->assertDatabaseCount('role_user', 1);

        $user->roles()->detach($adminRole->id);
        $this->assertDatabaseCount('role_user', 0);
    }

    public function test_deleting_user_removes_pivot_records_cascade(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Admin']);

        $user->roles()->attach($role->id);
        $this->assertDatabaseCount('role_user', 1);

        $user->delete();

        // Foreign key cascade cleans up pivot entries
        $this->assertDatabaseCount('role_user', 0);
        // But the Role itself remains
        $this->assertDatabaseCount('roles', 1);
    }
}

