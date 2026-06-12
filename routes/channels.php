<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    User::class,
    function (User $currentUser, User $user) {
        return $currentUser->id === $user->id;
    }
);
