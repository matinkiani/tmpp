# Flutter Chat App Backend Integration Guide

## Overview
This Laravel backend provides a complete chat system with real-time messaging using Laravel Reverb (WebSockets). The system supports public chat rooms, private rooms, and real-time message broadcasting.

## Server Configuration

### Development Servers
- **Laravel API**: `http://127.0.0.1:8000`
- **WebSocket Server (Reverb)**: `ws://127.0.0.1:8080`

### Starting the Servers
```bash
# Terminal 1: Start Laravel development server
php artisan serve --port=8000

# Terminal 2: Start Reverb WebSocket server
php artisan reverb:start --debug
```

## Authentication

### Login/Register (Username Only)
Users can join the chat by simply providing a username. No password required.

**Endpoint**: `POST /api/auth/login`

**Request Body**:
```json
{
    "username": "john_doe"
}
```

**Response**:
```json
{
    "user": {
        "id": 1,
        "name": "john_doe",
        "created_at": "2025-06-07T19:26:09.000000Z"
    },
    "token": "1|abc123..."
}
```

### Get Current User
**Endpoint**: `GET /api/auth/me`
**Headers**: `Authorization: Bearer {token}`

### Logout
**Endpoint**: `POST /api/auth/logout`
**Headers**: `Authorization: Bearer {token}`

## Chat Rooms

### Get All Available Rooms
**Endpoint**: `GET /api/chat-rooms`
**Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
    "chat_rooms": [
        {
            "id": 1,
            "name": "General Chat",
            "type": "public",
            "description": "Welcome to the general chat room!",
            "users_count": 5,
            "latest_message": {
                "id": 10,
                "content": "Hello everyone!",
                "created_at": "2025-06-07T19:26:09.000000Z",
                "user": {
                    "id": 2,
                    "name": "jane_doe"
                }
            },
            "created_at": "2025-06-07T19:21:04.000000Z"
        }
    ]
}
```

### Join a Room
**Endpoint**: `POST /api/chat-rooms/{room_id}/join`
**Headers**: `Authorization: Bearer {token}`

### Leave a Room
**Endpoint**: `POST /api/chat-rooms/{room_id}/leave`
**Headers**: `Authorization: Bearer {token}`

### Create Private Room
**Endpoint**: `POST /api/chat-rooms`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
    "name": "Private Discussion",
    "type": "private",
    "description": "A private room for team discussion",
    "user_ids": [2, 3, 4]
}
```

## Messages

### Get Messages from a Room
**Endpoint**: `GET /api/chat-rooms/{room_id}/messages`
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `page`: Page number (default: 1)
- `per_page`: Messages per page (default: 50, max: 100)

**Response**:
```json
{
    "messages": [
        {
            "id": 1,
            "content": "Hello everyone!",
            "type": "text",
            "metadata": null,
            "created_at": "2025-06-07T19:26:09.000000Z",
            "user": {
                "id": 2,
                "name": "jane_doe"
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 50,
        "total": 1,
        "has_more": false
    }
}
```

### Send a Message
**Endpoint**: `POST /api/chat-rooms/{room_id}/messages`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
    "content": "Hello everyone!",
    "type": "text"
}
```

**Response**:
```json
{
    "message": {
        "id": 1,
        "content": "Hello everyone!",
        "type": "text",
        "metadata": null,
        "user": {
            "id": 2,
            "name": "jane_doe"
        },
        "chat_room_id": 1,
        "created_at": "2025-06-07T19:26:09.000000Z"
    }
}
```

## Real-time WebSocket Events

### Connection
Connect to: `ws://127.0.0.1:8080/app/{REVERB_APP_KEY}`

### Authentication for Private Channels
For presence channels, you'll need to authenticate. Send a POST request to `/api/broadcasting/auth` with the channel name and socket_id.

### Available Channels

#### 1. Chat Room Channel (Presence Channel)
**Channel**: `private-chat-room.{room_id}`

**Events**:
- `message.sent`: New message in the room
- `user.joined`: User joined the room  
- `user.left`: User left the room

**Event Data Structure**:

```javascript
// message.sent
{
    "id": 1,
    "content": "Hello!",
    "type": "text",
    "metadata": null,
    "created_at": "2025-06-07T19:26:09.000000Z",
    "user": {
        "id": 2,
        "name": "jane_doe"
    },
    "chat_room_id": 1
}

// user.joined
{
    "user": {
        "id": 3,
        "name": "john_doe"
    },
    "chat_room_id": 1,
    "joined_at": "2025-06-07T19:26:09.000000Z"
}

// user.left
{
    "user": {
        "id": 3,
        "name": "john_doe"
    },
    "chat_room_id": 1,
    "left_at": "2025-06-07T19:26:09.000000Z"
}
```

## Frontend Integration Requirements

### HTTP Client Configuration
Your Flutter app needs to make HTTP requests to the Laravel API. Configure your HTTP client with:

**Base URL**: `http://127.0.0.1:8000/api`
**Authentication**: Bearer token in Authorization header
**Content-Type**: `application/json`

### WebSocket Client Configuration  
For real-time messaging, connect to the WebSocket server using a Pusher-compatible client.

**Connection Details**:
- **Host**: `127.0.0.1`
- **Port**: `8080`
- **App Key**: `6ago547nfocc4b6kesz7`
- **Full URL**: `ws://127.0.0.1:8080/app/6ago547nfocc4b6kesz7`
- **Protocol**: Use Pusher protocol (Laravel Reverb is Pusher-compatible)

**Recommended Flutter Package**: `pusher_channels_flutter`

### Authentication Flow for Private Channels
Private/presence channels require authentication:

1. **Auth Endpoint**: `http://127.0.0.1:8000/api/broadcasting/auth`
2. **Method**: POST
3. **Headers**: Include `Authorization: Bearer {token}`
4. **Body**: Send `channel_name` and `socket_id`

## Default Chat Rooms

The system comes with two default public rooms:
1. **General Chat** - Main public discussion room
2. **Random** - For casual conversations

## Error Handling

The API returns standard HTTP status codes:
- `200`: Success
- `201`: Created
- `401`: Unauthorized (invalid/missing token)
- `403`: Forbidden (no access to resource)
- `422`: Validation error
- `500`: Server error

Error responses include a `message` field with details.

## Rate Limiting

The API includes basic rate limiting. If you hit limits, you'll receive a `429` status code.

## Security Notes

1. This is a demo system with username-only authentication
2. For production, implement proper authentication with passwords
3. Add input validation and sanitization
4. Implement proper CORS settings for your domain
5. Use HTTPS in production
6. Add rate limiting and abuse prevention

## Testing

Use the provided `test_api.php` script to test all endpoints:
```bash
php test_api.php
```

This will test the complete flow: login, join room, send message, create private room, etc.
