<?php

namespace App\Listeners;

use App\User;
use App\Events\ThreadReceivedNewReply;
use App\Notifications\YouWereMentioned;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyMentionedUsers
{
    /**
     * Handle the event.
     *
     * @param  ThreadReceivedNewReply  $event
     * @return void
     */
    public function handle(ThreadReceivedNewReply $event)
    {
        //this way...
        $users = User::whereIn('name', $event->reply->mentionedUsers())
            ->get()
            ->each(function ($user) use ($event) {
                $user->notify(new YouWereMentioned($event->reply));
            });

        // OR
        // $mentionedUsers = $event->reply->mentionedUsers();
        //
		// foreach ($mentionedUsers as $name) {
		// 	$user = User::whereName($name)->first();
        //
		// 	if ($user) {
		// 		$user->notify(new YouWereMentioned($event->reply));
		// 	}
		// }

        // OR this way
        // collect($event->reply->mentionedUsers())
        //     ->map(function ($name) {
        //         return User::whereName($name)->first();
        //     })
        //     ->filter() // if there is no user (null returned) with $name cut it off.
        //     ->each(function ($user) use ($event) {
        //         $user->notify(new YouWereMentioned($event->reply));
        //     });
    }
}
