# Chat App Backend

A real-time chat application backend built with Laravel 12 and Laravel Reverb for WebSocket communication.

## Features

- **Username-only Authentication** - Simple login with just a username
- **Public & Private Chat Rooms** - Support for both room types
- **Real-time Messaging** - WebSocket-powered instant messaging
- **Message History** - Persistent chat history with pagination
- **User Presence** - Track when users join/leave rooms
- **RESTful API** - Complete API for frontend integration

## Tech Stack

- **Laravel 12** - PHP framework
- **Laravel Reverb** - WebSocket server for real-time communication
- **Laravel Sanctum** - API token authentication
- **SQLite** - Database (easily switchable to MySQL/PostgreSQL)

## Quick Start

### 1. Installation

```bash
# Clone the repository
git clone <your-repo-url>
cd chat-backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations and seed default rooms
php artisan migrate --seed
```

### 2. Configuration

The `.env` file contains important WebSocket configuration:

```env
REVERB_APP_ID=459374
REVERB_APP_KEY=6ago547nfocc4b6kesz7
REVERB_APP_SECRET=cgnfaj05lfw5lihbseuy
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
```

**Note:** The `REVERB_APP_KEY` is used by frontend clients to connect to the WebSocket server.

### 3. Running the Application

Start both servers in separate terminals:

```bash
# Terminal 1: Laravel API Server
php artisan serve --port=8000

# Terminal 2: WebSocket Server  
php artisan reverb:start --debug
```

Your application will be available at:
- **API Endpoint**: `http://127.0.0.1:8000/api`
- **WebSocket Server**: `ws://127.0.0.1:8080`

## API Documentation

### Authentication

#### Login (Username Only)
```http
POST /api/auth/login
Content-Type: application/json

{
    "username": "john_doe"
}
```

Response:
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

#### Get Current User
```http
GET /api/auth/me
Authorization: Bearer {token}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Chat Rooms

#### Get All Rooms
```http
GET /api/chat-rooms
Authorization: Bearer {token}
```

#### Join a Room
```http
POST /api/chat-rooms/{room_id}/join
Authorization: Bearer {token}
```

#### Leave a Room
```http
POST /api/chat-rooms/{room_id}/leave
Authorization: Bearer {token}
```

#### Create Private Room
```http
POST /api/chat-rooms
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Team Discussion",
    "type": "private",
    "description": "Private team chat",
    "user_ids": [2, 3, 4]
}
```

### Messages

#### Get Messages
```http
GET /api/chat-rooms/{room_id}/messages?page=1&per_page=50
Authorization: Bearer {token}
```

#### Send Message
```http
POST /api/chat-rooms/{room_id}/messages
Authorization: Bearer {token}
Content-Type: application/json

{
    "content": "Hello everyone!",
    "type": "text"
}
```

## WebSocket Events

### Connection
Frontend clients should connect to:
```
ws://127.0.0.1:8080/app/6ago547nfocc4b6kesz7
```

### Channels & Events

#### Chat Room Channel
**Channel**: `private-chat-room.{room_id}`

**Events**:
- `message.sent` - New message in room
- `user.joined` - User joined room
- `user.left` - User left room

#### Event Data Examples

**Message Sent:**
```json
{
    "id": 1,
    "content": "Hello!",
    "type": "text",
    "created_at": "2025-06-07T19:26:09.000000Z",
    "user": {
        "id": 2,
        "name": "jane_doe"
    },
    "chat_room_id": 1
}
```

**User Joined:**
```json
{
    "user": {
        "id": 3,
        "name": "john_doe"
    },
    "chat_room_id": 1,
    "joined_at": "2025-06-07T19:26:09.000000Z"
}
```

## Database Schema

### Tables

- **users** - User accounts
- **chat_rooms** - Chat room information
- **messages** - All chat messages
- **chat_room_users** - User membership in rooms

### Default Rooms

The system comes with two default public rooms:
1. **General Chat** - Main discussion room
2. **Random** - Casual conversations

## Testing

Run the included test script to verify all endpoints:

```bash
php test_api.php
```

This will test:
- User login
- Getting chat rooms
- Joining rooms
- Sending messages
- Creating private rooms
- Logout

## Production Deployment

### Environment Variables

For production, update these variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (switch to MySQL/PostgreSQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Generate new Reverb keys for production
REVERB_APP_ID=your_production_id
REVERB_APP_KEY=your_production_key
REVERB_APP_SECRET=your_production_secret
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

### Security Considerations

1. **Generate new Reverb keys** for production
2. **Use HTTPS** in production environments
3. **Configure CORS** for your frontend domain
4. **Set up proper authentication** (consider adding passwords)
5. **Implement rate limiting** and abuse prevention
6. **Use a proper database** (MySQL/PostgreSQL)

## Frontend Integration

For frontend developers, see `FLUTTER_INTEGRATION_GUIDE.md` for detailed integration instructions including:
- HTTP client setup
- WebSocket connection
- Event handling
- Authentication flow

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
