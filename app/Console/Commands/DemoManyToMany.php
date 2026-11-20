<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DemoManyToMany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:demo-many-to-many';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demonstrate Laravel Eloquent Many-to-Many relationship with SQLite';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Laravel Many-to-Many Eloquent + SQLite Demonstration ===');
        $this->newLine();

        // 1. Ensure Roles exist
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $editorRole = Role::firstOrCreate(['name' => 'Editor']);
        $subscriberRole = Role::firstOrCreate(['name' => 'Subscriber']);
        $this->info("1. Roles in database: {$adminRole->name}, {$editorRole->name}, {$subscriberRole->name}");

        // 2. Create User
        $user = User::create([
            'name' => 'Sir Kevin',
            'email' => 'admin_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->info("2. Created User: [ID: {$user->id}] {$user->name} ({$user->email})");

        // 3. Attach multiple roles via ->attach()
        $user->roles()->attach([$adminRole->id, $editorRole->id]);
        $this->info("3. Attached 'Admin' and 'Editor' roles using \$user->roles()->attach(...)");

        // Display current user roles
        $loadedUser = User::with('roles')->find($user->id);
        $rows = [];
        foreach ($loadedUser->roles as $role) {
            $rows[] = [
                $loadedUser->name,
                $role->id,
                $role->name,
                $role->pivot->created_at ? $role->pivot->created_at->toDateTimeString() : 'N/A',
            ];
        }
        $this->table(['User', 'Role ID', 'Role Name', 'Pivot Assigned At'], $rows);

        // 4. Inverse relationship ($role->users)
        $this->newLine();
        $this->info("4. Inverse Relationship (\$adminRole->users):");
        $adminUsers = $adminRole->users()->pluck('name')->implode(', ');
        $this->line("   -> Users holding 'Admin' role: {$adminUsers}");

        // 5. Using ->sync() to replace roles
        $this->newLine();
        $this->info("5. Syncing roles using \$user->roles()->sync([\$subscriberRole->id]):");
        $user->roles()->sync([$subscriberRole->id]);

        $syncedUser = User::with('roles')->find($user->id);
        $syncedRoles = $syncedUser->roles->pluck('name')->implode(', ');
        $this->line("   -> User roles after sync: {$syncedRoles} (previous roles cleanly detached!)");

        $this->newLine();
        $this->info('Done! Many-to-many relationship with SQLite is working properly.');

        return Command::SUCCESS;
    }
}
