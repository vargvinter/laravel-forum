<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProfilesTest extends TestCase
{
	use DatabaseMigrations;

	/** @test */
    public function a_user_has_profile()
    {
    	$user = create(\App\User::class);

    	$this->get('/profiles/' . $user->name)
    		->assertSee($user->name);
    }

	/** @test */
    public function profiles_display_all_threads_created_by_the_associated_user()
    {
        $this->signIn();

    	$thread = create(\App\Thread::class, ['user_id' => auth()->id()]);

    	$this->get('/profiles/' . auth()->user()->name)
    		->assertSee($thread->title)
    		->assertSee($thread->body);
    }
}
