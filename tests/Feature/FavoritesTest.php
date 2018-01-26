<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FavoritesTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function a_guest_can_not_favorite_anything()
    {        
        $this->withExceptionHandling()
            ->post('replies/1/favorites')
            ->assertRedirect('/login');
    }

	/** @test */
	public function an_authenticated_user_can_favorite_any_reply()
	{
        $this->signIn();

        $reply = create(\App\Reply::class);

        $this->post('replies/' . $reply->id . '/favorites');

        $this->assertCount(1, $reply->favorites);
    }

    /** @test */
    public function an_authenticated_user_can_unfavorite_any_reply()
    {
        $this->signIn();

        $reply = create(\App\Reply::class);

        $reply->favorite();

        $this->delete('replies/' . $reply->id . '/favorites');

        $this->assertCount(0, $reply->favorites);
    }

    /** @test */
    public function an_authenticated_user_may_only_favorite_a_reply_once($value='')
    {
        $this->signIn();

        $reply = create(\App\Reply::class);

        try {
            $this->post('replies/' . $reply->id . '/favorites');
            $this->post('replies/' . $reply->id . '/favorites');
        } catch(\Exception $e) {
            $this->fail('Did not expect to insert the same record set twice.');
        }


        $this->assertCount(1, $reply->favorites);
    }

}
