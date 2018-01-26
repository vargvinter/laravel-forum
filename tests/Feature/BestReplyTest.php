<?php

namespace Tests\Feature;

use DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BestReplyTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_thread_creater_may_mark_any_reply_as_the_best_reply()
    {
        $this->signIn();

        $thread = create(\App\Thread::class, ['user_id' => auth()->id()]);

        $replies = create(\App\Reply::class, ['thread_id' => $thread->id], 2);

        $this->assertFalse($replies[1]->fresh()->isBest());

        $this->postJson(route('best-replies.store', [$replies[1]->id]));

        $this->assertTrue($replies[1]->fresh()->isBest());
    }

    /** @test */
    public function only_the_thread_creator_may_mark_a_reply_as_the_best()
    {
        $this->withExceptionHandling();

        $this->signIn();

        $thread = create(\App\Thread::class, ['user_id' => auth()->id()]);

        $replies = create(\App\Reply::class, ['thread_id' => $thread->id], 2);

        $this->signIn(create(\App\User::class));

        $this->postJson(route('best-replies.store', [$replies[1]->id]))->assertStatus(403);

        $this->assertFalse($replies[1]->fresh()->isBest());
    }

    /** @test */
    public function if_the_best_reply_is_deleted_then_the_thread_is_properly_updated_to_reflect_that()
    {
        // Tests are performed on SQLite DB where foreign keys constraints
        // are disabled by default, so we must turn them on.
        // We can move this line to TestCase@setUp()...
        // On MySQL everything will work without this statement.
        DB::statement('PRAGMA foreign_keys=on;');

        $this->signIn();

        $reply = create(\App\Reply::class, ['user_id' => auth()->id()]);

        $reply->thread->markBestReply($reply);

        $this->deleteJson(route('replies.destroy', $reply));

        $this->assertNull($reply->thread->fresh()->best_reply_id);
    }
}
