<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DemoOneToOne extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:demo-one-to-one';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demonstrate Laravel Eloquent One-to-One relationship with SQLite';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Laravel One-to-One Eloquent + SQLite Demonstration ===');
        $this->newLine();

        // 1. Create a new User
        $randomEmail = 'user_' . Str::random(5) . '@example.com';
        $user = User::create([
            'name' => 'Sir Kevin',
            'email' => $randomEmail,
            'password' => bcrypt('secret123'),
        ]);
        $this->info("1. Created User: [ID: {$user->id}] {$user->name} ({$user->email})");

        // 2. Create the associated Profile via relationship
        $profile = $user->profile()->create([
            'phone_number' => '+63 912 345 6789',
            'bio' => 'Full-stack developer experimenting with Laravel and SQLite.',
            'avatar_url' => 'https://example.com/avatar.png',
        ]);
        $this->info("2. Created Profile via \$user->profile()->create(): [Profile ID: {$profile->id}]");

        // 3. Retrieve Profile via User
        $loadedUser = User::find($user->id);
        $this->line("   -> Accessing \$user->profile->phone_number: " . $loadedUser->profile->phone_number);
        $this->line("   -> Accessing \$user->profile->bio: " . $loadedUser->profile->bio);

        // 4. Access inverse relationship (Profile -> User)
        $loadedProfile = Profile::find($profile->id);
        $this->info("3. Inverse Relationship (\$profile->user):");
        $this->line("   -> Profile belongs to user: " . $loadedProfile->user->name . " ({$loadedProfile->user->email})");

        // 5. Eager Loading (Avoids N+1 queries)
        $this->newLine();
        $this->info("4. Eager Loading Query (User::with('profile')):");
        $usersWithProfiles = User::with('profile')->latest('id')->take(3)->get();

        $rows = [];
        foreach ($usersWithProfiles as $u) {
            $rows[] = [
                $u->id,
                $u->name,
                $u->email,
                $u->profile?->phone_number ?? 'No Profile',
                $u->profile?->bio ?? 'N/A',
            ];
        }

        $this->table(['User ID', 'Name', 'Email', 'Phone', 'Bio'], $rows);

        $this->info('Done! One-to-one relationship with SQLite is working properly.');
        return Command::SUCCESS;
    }
}
