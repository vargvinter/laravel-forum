<?php

namespace Tests\Feature;

use App\Rules\Recaptcha;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use App\Activity;

class CreateThreadsTest extends TestCase
{
	use DatabaseMigrations;

	public function setUp()
	{
		parent::setUp();

		// Bind this mock with the Laravel container.
		app()->singleton(Recaptcha::class, function () {
			// Use stub to pretend that Recaptcha validation returns true.
			return \Mockery::mock(Recaptcha::class, function ($m) {
				// Method 'passes' should be called and true should be returned.
				$m->shouldReceive('passes')->andReturn(true);
			});
		});
		// PS. Recaptha::class must be injected into ThreadsController@store method.
	}

	/** @test */
	public function guests_may_not_create_threads()
	{
        $this->withExceptionHandling();

        $this->get('/threads/create')
            ->assertRedirect(route('login'));

        $this->post(route('threads'))
            ->assertRedirect(route('login'));
	}

	/** @test */
	public function new_users_must_first_confirm_their_email_address_before_creating_threads()
	{
		$user = factory(\App\User::class)->states('unconfirmed')->create();

		$this->signIn($user);

		$thread = make(\App\Thread::class);

		$this->post(route('threads'), $thread->toArray())
			->assertRedirect('/threads')
			->assertSessionHas('flash', 'You must first confirm your email address.');
	}

    /** @test */
    public function a_user_can_create_forum_threads()
    {
		$response = $this->publishThread(['title' => 'Some title', 'body' => 'Some body']);

    	$this->get($response->headers->get('Location'))
    		 ->assertSee('Some title')
    		 ->assertSee('Some body');
    }

    /** @test */
    public function a_thread_requires_a_title()
    {
        $this->publishThread(['title' => null])
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_thread_requires_a_body()
    {
        $this->publishThread(['body' => null])
            ->assertSessionHasErrors('body');
    }

	/** @test */
	public function a_thread_requires_recaptcha_verification()
	{
		// Unbind Recaptcha::class from the container to really test this rule.
		unset(app()[Recaptcha::class]);

		$this->publishThread(['g-recaptcha-response' => 'token'])
            ->assertSessionHasErrors('g-recaptcha-response');
	}

    /** @test */
    public function a_thread_requires_a_valid_channel()
    {
        factory(\App\Channel::class, 2)->create();

        $this->publishThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 999])
            ->assertSessionHasErrors('channel_id');
    }

	/** @test */
	public function a_thread_requires_a_unique_slug()
	{
		$this->signIn();

		$thread = create(\App\Thread::class, ['title' => 'Foo Title']);

		$this->assertEquals($thread->fresh()->slug, 'foo-title');

		$thread = $this->postJson(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

		$this->assertEquals("foo-title-{$thread['id']}", $thread['slug']);
	}

	/** @test */
	public function a_thread_with_a_title_that_ends_with_a_number_should_generate_the_proper_slug()
	{
		$this->signIn();

		$thread = create(\App\Thread::class, ['title' => 'Some title 24']);

		$thread = $this->postJson(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

		$this->assertEquals("some-title-24-{$thread['id']}", $thread['slug']);
	}

    /** @test */
    public function unauthorized_users_may_not_delete_threads()
    {
        $this->withExceptionHandling();

        $thread = create(\App\Thread::class);

        $this->delete($thread->path())
            ->assertRedirect('/login');

        $this->signIn();
        $this->delete($thread->path())
            ->assertStatus(403);
    }

    /** @test */
    public function authorized_users_can_delete_threads()
    {
        $this->signIn();

        $thread = create(\App\Thread::class, ['user_id' => auth()->id()]);
        $reply = create(\App\Reply::class, ['thread_id' => $thread->id]);

        $response = $this->json('DELETE', $thread->path());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

        // this way...
        $this->assertDatabaseMissing('activities', [
            'subject_id' => $thread->id,
            'subject_type' => get_class($thread)
        ]);

        $this->assertDatabaseMissing('activities', [
            'subject_id' => $reply->id,
            'subject_type' => get_class($reply)
        ]);

        // OR this way...
        $this->assertEquals(0, Activity::count());
    }

    protected function publishThread($overrides = [])
    {
        $this->withExceptionHandling()->signIn();

        $thread = make(\App\Thread::class, $overrides);

        return $this->post(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token']);
    }
}
