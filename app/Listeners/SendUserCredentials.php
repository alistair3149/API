<?php

namespace App\Listeners;

use App\Mail\UserRegistered as UserRegisteredMail;
use App\Events\UserRegistered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendUserCredentials implements ShouldQueue
{

    /**
     * Create the event listener.
     *
     * @internal param Mailer $mailer
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  UserRegistered  $event
     * @return void
     */
    public function handle(UserRegistered $event)
    {
        $user = $event->user;
        Mail::to($user->email)->send(new UserRegisteredMail($user));
    }
}
