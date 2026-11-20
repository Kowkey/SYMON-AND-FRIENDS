<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OneToManyRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_many_posts(): void
    {
        $user = User::factory()->create();

        $user->posts()->createMany([
            ['title' => 'First Post', 'body' => 'Body of first post'],
            ['title' => 'Second Post', 'body' => 'Body of second post'],
            ['title' => 'Third Post', 'body' => 'Body of third post'],
        ]);

        $this->assertDatabaseCount('posts', 3);
        $this->assertCount(3, $user->posts);
        $this->assertEquals('First Post', $user->posts->first()->title);
    }

    public function test_post_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'title'   => 'Sample Title',
            'body'    => 'Sample Body Content',
        ]);

        $this->assertEquals($user->id, $post->user->id);
        $this->assertEquals($user->email, $post->user->email);
    }

    public function test_deleting_user_cascades_and_deletes_all_posts(): void
    {
        $user = User::factory()->create();
        $user->posts()->createMany([
            ['title' => 'Post 1', 'body' => 'Content 1'],
            ['title' => 'Post 2', 'body' => 'Content 2'],
        ]);

        $this->assertDatabaseCount('posts', 2);

        $user->delete();

        // Foreign key cascade should delete all related posts
        $this->assertDatabaseCount('posts', 0);
    }
}

