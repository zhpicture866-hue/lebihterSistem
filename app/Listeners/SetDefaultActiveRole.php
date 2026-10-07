<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;

class SetDefaultActiveRole
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        $valid = $user->active_role
            && $user->roles->contains('id', $user->active_role);

        if (!$valid) {
            $first = $user->roles->first();

            if ($first) {
                $user->active_role = $first->id;
                $user->save();
            }
        }
    }
}
