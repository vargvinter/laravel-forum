<?php

namespace App\Http\Controllers;

use App\Reply;
use App\Thread;
use App\User;
use App\Http\Requests\CreatePostRequest;
use Gate;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth', ['except' => 'index']);
	}

    public function index($channelId, Thread $thread)
    {
        return $thread->replies()->paginate(20);
    }

    public function store($channelId, Thread $thread, CreatePostRequest $request)
    {
		if ($thread->locked) {
			return response('Thread is locked.', 422);
		}

		/* Load 'owner' relationship to get information about reply's owner for Reply.vue. */
		return $thread->addReply([
			'body' => request('body'),
			'user_id' => auth()->id()
		])->load('owner');
    }

    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);

		request()->validate([
			'body' => 'required|spamfree'
		]);

		$reply->update([
			'body' => request('body')
		]);
    }

    public function destroy(Reply $reply)
    {
        $this->authorize('update', $reply);

        $reply->delete();

        if (request()->expectsJson()) {
            return response(['status' => 'Reply deleted']);
        }

        return back();
    }
}
