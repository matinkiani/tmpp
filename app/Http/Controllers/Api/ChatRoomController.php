<?php

namespace App\Http\Controllers\Api;

use App\Events\UserJoinedRoom;
use App\Events\UserLeftRoom;
use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Http\Request;

class ChatRoomController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all public rooms and private rooms where user is a member
        $chatRooms = ChatRoom::active()
            ->where(function ($query) use ($user) {
                $query->where('type', 'public')
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->with(['users:id,name', 'latestMessage.user:id,name'])
            ->withCount('users')
            ->get();

        return response()->json([
            'chat_rooms' => $chatRooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'type' => $room->type,
                    'description' => $room->description,
                    'users_count' => $room->users_count,
                    'latest_message' => $room->latestMessage?->first() ? [
                        'id' => $room->latestMessage->first()->id,
                        'content' => $room->latestMessage->first()->content,
                        'created_at' => $room->latestMessage->first()->created_at,
                        'user' => $room->latestMessage->first()->user,
                    ] : null,
                    'created_at' => $room->created_at,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:public,private',
            'description' => 'nullable|string|max:1000',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $chatRoom = ChatRoom::create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'created_by' => $request->user()->id,
        ]);

        // Add creator to the room
        $chatRoom->users()->attach($request->user()->id, [
            'is_admin' => true,
            'joined_at' => now(),
        ]);

        // Add other users if specified
        if ($request->user_ids) {
            $userIds = collect($request->user_ids)->filter(function ($id) use ($request) {
                return $id !== $request->user()->id;
            });

            $chatRoom->users()->attach($userIds, [
                'joined_at' => now(),
            ]);
        }

        $chatRoom->load(['users:id,name']);

        return response()->json([
            'chat_room' => [
                'id' => $chatRoom->id,
                'name' => $chatRoom->name,
                'type' => $chatRoom->type,
                'description' => $chatRoom->description,
                'users' => $chatRoom->users,
                'created_at' => $chatRoom->created_at,
            ],
        ], 201);
    }

    public function show(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        // Check if user has access to this room
        if ($chatRoom->type === 'private' && !$chatRoom->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chatRoom->load(['users:id,name']);

        return response()->json([
            'chat_room' => [
                'id' => $chatRoom->id,
                'name' => $chatRoom->name,
                'type' => $chatRoom->type,
                'description' => $chatRoom->description,
                'users' => $chatRoom->users,
                'created_at' => $chatRoom->created_at,
            ],
        ]);
    }

    public function join(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        // Check if already a member
        if ($chatRoom->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Already a member of this room'], 422);
        }

        // For private rooms, only allow joining if invited (for now, allow public rooms only)
        if ($chatRoom->type === 'private') {
            return response()->json(['message' => 'Cannot join private rooms without invitation'], 403);
        }

        $chatRoom->users()->attach($user->id, [
            'joined_at' => now(),
        ]);

        // Broadcast user joined event
        broadcast(new UserJoinedRoom($user, $chatRoom));

        return response()->json([
            'message' => 'Successfully joined the room',
        ]);
    }

    public function leave(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        // Check if user is a member
        if (!$chatRoom->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Not a member of this room'], 422);
        }

        $chatRoom->users()->detach($user->id);

        // Broadcast user left event
        broadcast(new UserLeftRoom($user, $chatRoom));

        return response()->json([
            'message' => 'Successfully left the room',
        ]);
    }
}
