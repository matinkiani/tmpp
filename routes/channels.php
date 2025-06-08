<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoom;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Chat room presence channel
Broadcast::channel('chat-room.{roomId}', function ($user, $roomId) {
    $chatRoom = ChatRoom::find($roomId);

    if (!$chatRoom) {
        return false;
    }

    // For public rooms, allow anyone to join
    if ($chatRoom->type === 'public') {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

    // For private rooms, check if user is a member
    if ($chatRoom->type === 'private' && $chatRoom->users()->where('user_id', $user->id)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

    return false;
});
