<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DemoOneToMany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:demo-one-to-many';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demonstrate Laravel Eloquent One-to-Many relationship with SQLite';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Laravel One-to-Many Eloquent + SQLite Demonstration ===');
        $this->newLine();

        // 1. Create a User
        $user = User::create([
            'name' => 'Sir Kevin',
            'email' => 'author_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->info("1. Created Author (User): [ID: {$user->id}] {$user->name} ({$user->email})");

        // 2. Create multiple Posts for this User (One-to-Many)
        $postsData = [
            [
                'title' => 'Getting Started with SQLite in Laravel',
                'body'  => 'SQLite is lightweight, serverless, and perfect for fast prototyping.',
            ],
            [
                'title' => 'Understanding One-to-Many Relationships',
                'body'  => 'A user can author many posts, but each post belongs to one user.',
            ],
            [
                'title' => 'Eloquent Eager Loading Tips',
                'body'  => 'Use User::with("posts") to avoid N+1 query performance issues.',
            ],
        ];

        // Using createMany on the relation
        $createdPosts = $user->posts()->createMany($postsData);
        $this->info("2. Created " . count($createdPosts) . " Posts via \$user->posts()->createMany()");

        // 3. Accessing posts from User
        $loadedUser = User::with('posts')->find($user->id);
        $this->info("3. Retrieving posts via \$user->posts (Count: {$loadedUser->posts->count()}):");

        $rows = [];
        foreach ($loadedUser->posts as $post) {
            $rows[] = [
                $post->id,
                $post->title,
                Str::limit($post->body, 40),
                $post->user->name . " (ID: {$post->user_id})",
            ];
        }

        $this->table(['Post ID', 'Title', 'Body Preview', 'Author (Inverse Rel)'], $rows);

        // 4. Inverse relationship test
        $firstPost = Post::find($createdPosts->first()->id);
        $this->info("4. Inverse relationship (\$post->user):");
        $this->line("   -> Post #{$firstPost->id} '{$firstPost->title}' was written by: {$firstPost->user->name}");

        $this->newLine();
        $this->info('Done! One-to-many relationship with SQLite is working properly.');

        return Command::SUCCESS;
    }
}
