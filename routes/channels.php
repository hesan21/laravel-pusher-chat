<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// First argument $user is authenticated user, second $id translated from above wild card {id}
Broadcast::channel('chat.{id}', function ($user, $id) {
//    return (int) $user->id === (int) $id;
    return true;
});
