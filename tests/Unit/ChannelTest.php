<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ChannelTest extends TestCase
{
	use DatabaseMigrations;
	
    /** @test */
    public function it_consists_of_threads()
    {
        $channel = create(\App\Channel::class);
        $thread = create(\App\Thread::class, ['channel_id' => $channel->id]);

        $this->assertTrue($channel->threads->contains($thread));
    }
}
