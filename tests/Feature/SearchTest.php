<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_search_threads()
    {
        // Turn off null driver for scout only for this test.
        config(['scout.driver' => 'algolia']);

        $search = 'foobar';

        create(\App\Thread::class, [], 2);
        create(\App\Thread::class, ['body' => "A thread with a {$search} term."], 2);

        // $results may be empty because Algolia needs time for indexing above threads.
        // Keep querying Algolia until $results collection is not empty.
        do {
            sleep(.25);

            $results = $this->getJson("/threads/search?q={$search}")->json()['data'];
        } while (empty($results));

        $this->assertCount(2, $results);

        // Delete created threads for a purpose of this test from the elasticsearch index.
        \App\Thread::latest()->take(4)->unsearchable();
    }
}
