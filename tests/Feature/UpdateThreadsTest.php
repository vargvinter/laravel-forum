<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateThreadsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();

        $this->signIn();
    }

    /** @test */
    public function a_thread_requires_a_title_and_body_to_be_updated()
    {
        $thread = create(\App\Thread::class, ['user_id' => auth()->id()]);

        $this->patch($thread->path(), [
            'title' => 'Changed title'
        ])->assertSessionHasErrors('body');

        $this->patch($thread->path(), [
            'body' => 'Changed body'
        ])->assertSessionHasErrors('title');
    }

    /** @test */
    public function unauthorized_users_may_not_update_the_threads()
    {
        $thread = create(\App\Thread::class, ['user_id' => create(\App\User::class)->id]);

        $this->patch($thread->path(), [])
            ->assertStatus(403);
    }

    /** @test */
    public function a_thread_can_be_updated_by_its_creator()
    {
        $thread = create(\App\Thread::class, ['user_id' => auth()->id()]);

        $this->patch($thread->path(), [
            'title' => 'Changed title',
            'body' => 'Changed body'
        ]);

        tap($thread->fresh(), function($thread) {
            $this->assertEquals('Changed title', $thread->title);
            $this->assertEquals('Changed body', $thread->body);
        });
    }
}