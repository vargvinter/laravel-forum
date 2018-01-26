<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];

    public function subject()
    {
    	return $this->morphTo();
    }

    public static function feed(User $user, $take = 50)
    {
    	/* We don't need to access 'activity' relationship so let's use static. */
    	//return $user->activity()

    	return static::whereUserId($user->id)
    		->latest()
    		->with('subject')
    		->take($take)
    		->get()
    		->groupBy(function($activity) {
    			return $activity->created_at->format('Y-m-d');
    		});
    }
}
