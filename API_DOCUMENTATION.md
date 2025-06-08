# Chat App API Documentation

## Base URL
`http://127.0.0.1:8000/api`

## Authentication
Uses Laravel Sanctum with bearer tokens. Include `Authorization: Bearer {token}` header for protected routes.

## Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/login` | Login with username only |
| GET | `/auth/me` | Get current user |
| POST | `/auth/logout` | Logout and revoke token |

**Login Request:**
```json
{ "username": "john_doe" }
```

**Login Response:**
```json
{
    "user": {
        "id": 1,
        "name": "john_doe",
        "created_at": "2025-06-08T10:30:00.000000Z"
    },
    "token": "1|abc123def456..."
}
```

**Get Current User Response:**
```json
{
    "user": {
        "id": 1,
        "name": "john_doe",
        "created_at": "2025-06-08T10:30:00.000000Z"
    }
}
```

**Logout Response:**
```json
{
    "message": "Logged out successfully"
}
```

### Chat Rooms
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/chat-rooms` | Get all rooms |
| GET | `/chat-rooms/{id}` | Get specific room |
| POST | `/chat-rooms` | Create private room |
| POST | `/chat-rooms/{id}/join` | Join a room |
| POST | `/chat-rooms/{id}/leave` | Leave a room |

**Create Room Request:**
```json
{
    "name": "Private Room",
    "type": "private",
    "description": "Description",
    "user_ids": [2, 3]
}
```

**Get All Rooms Response:**
```json
{
    "chat_rooms": [
        {
            "id": 1,
            "name": "General Chat",
            "type": "public",
            "description": "Main chat room for everyone",
            "users_count": 5,
            "latest_message": {
                "id": 123,
                "content": "Hello everyone!",
                "created_at": "2025-06-08T10:30:00.000000Z",
                "user": {
                    "id": 1,
                    "name": "john_doe"
                }
            },
            "created_at": "2025-06-08T09:00:00.000000Z"
        }
    ]
}
```

**Get/Create Room Response:**
```json
{
    "chat_room": {
        "id": 1,
        "name": "General Chat",
        "type": "public",
        "description": "Main chat room for everyone",
        "users": [
            {
                "id": 1,
                "name": "john_doe"
            },
            {
                "id": 2,
                "name": "jane_doe"
            }
        ],
        "created_at": "2025-06-08T09:00:00.000000Z"
    }
}
```

**Join/Leave Room Response:**
```json
{
    "message": "Successfully joined the room"
}
```

### Messages
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/chat-rooms/{id}/messages` | Get messages (paginated) |
| POST | `/chat-rooms/{id}/messages` | Send message |
| GET | `/chat-rooms/{id}/messages/{messageId}` | Get specific message |
| POST | `/chat-rooms/{id}/messages/mark-read` | Mark messages as read |

**Send Message Request:**
```json
{
    "content": "Hello everyone!",
    "type": "text",
    "metadata": {}
}
```

**Get Messages Response:**
```json
{
    "messages": [
        {
            "id": 123,
            "content": "Hello everyone!",
            "type": "text",
            "metadata": null,
            "user": {
                "id": 1,
                "name": "john_doe"
            },
            "chat_room_id": 1,
            "created_at": "2025-06-08T10:30:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 3,
        "per_page": 50,
        "total": 125,
        "has_more": true
    }
}
```

**Send/Get Message Response:**
```json
{
    "message": {
        "id": 123,
        "content": "Hello everyone!",
        "type": "text",
        "metadata": null,
        "user": {
            "id": 1,
            "name": "john_doe"
        },
        "chat_room_id": 1,
        "created_at": "2025-06-08T10:30:00.000000Z"
    }
}
```

**Mark as Read Response:**
```json
{
    "message": "Messages marked as read"
}
```

## WebSocket Configuration

**Connection URL:** `ws://127.0.0.1:8080/app/6ago547nfocc4b6kesz7`

### Channels & Events
- **Channel:** `private-chat-room.{room_id}`
- **Events:** `message.sent`, `user.joined`, `user.left`

### Authentication
Private channels require authentication via `POST /api/broadcasting/auth` with Bearer token.

## Error Responses

### Standard Error Format
```json
{
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (missing or invalid token)
- `403` - Forbidden (access denied)
- `404` - Not Found
- `422` - Unprocessable Entity (validation errors)
- `500` - Internal Server Error

### Example Error Responses

**Validation Error (422):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "username": ["The username field is required."],
        "content": ["The content field is required."]
    }
}
```

**Unauthorized Access (401):**
```json
{
    "message": "Unauthenticated."
}
```

**Forbidden Access (403):**
```json
{
    "message": "Unauthorized"
}
```
