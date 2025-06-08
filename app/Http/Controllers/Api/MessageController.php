<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        // Check if user has access to this room
        if ($chatRoom->type === 'private' && !$chatRoom->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 50);

        $messages = $chatRoom->messages()
            ->with('user:id,name')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'has_more' => $messages->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        // Check if user has access to this room
        if ($chatRoom->type === 'private' && !$chatRoom->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:10000',
            'type' => 'in:text,image,file',
            'metadata' => 'nullable|array',
        ]);

        $message = Message::create([
            'user_id' => $user->id,
            'chat_room_id' => $chatRoom->id,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
            'metadata' => $request->metadata,
        ]);

        $message->load('user:id,name');

        // Broadcast the message
        broadcast(new MessageSent($message));

        return response()->json([
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
                'type' => $message->type,
                'metadata' => $message->metadata,
                'user' => $message->user,
                'chat_room_id' => $message->chat_room_id,
                'created_at' => $message->created_at,
            ],
        ], 201);
    }

    public function show(Request $request, ChatRoom $chatRoom, Message $message)
    {
        $user = $request->user();

        // Check if user has access to this room
        if ($chatRoom->type === 'private' && !$chatRoom->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if message belongs to this chat room
        if ($message->chat_room_id !== $chatRoom->id) {
            return response()->json(['message' => 'Message not found in this room'], 404);
        }

        $message->load('user:id,name');

        return response()->json([
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
                'type' => $message->type,
                'metadata' => $message->metadata,
                'user' => $message->user,
                'chat_room_id' => $message->chat_room_id,
                'created_at' => $message->created_at,
            ],
        ]);
    }

    public function markAsRead(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        // Check if user has access to this room
        if ($chatRoom->type === 'private' && !$chatRoom->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Mark all unread messages in this room as read for this user
        $chatRoom->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Messages marked as read',
        ]);
    }
}
