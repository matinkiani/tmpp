#!/usr/bin/env php
<?php

// Simple API test script for the chat system

$baseUrl = 'http://127.0.0.1:8000/api';

function apiRequest($method, $endpoint, $data = null, $token = null) {
    global $baseUrl;

    $curl = curl_init();
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrl . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $data ? json_encode($data) : null,
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    echo "[$method] $endpoint - HTTP $httpCode\n";
    echo "Response: " . $response . "\n\n";

    return json_decode($response, true);
}

echo "=== Testing Chat API ===\n\n";

// Test 1: Login
echo "1. Testing login...\n";
$loginResponse = apiRequest('POST', '/auth/login', [
    'username' => 'test_user_' . time()
]);

if (!isset($loginResponse['token'])) {
    echo "Login failed!\n";
    exit(1);
}

$token = $loginResponse['token'];
$userId = $loginResponse['user']['id'];

// Test 2: Get current user
echo "2. Testing get current user...\n";
apiRequest('GET', '/auth/me', null, $token);

// Test 3: Get chat rooms
echo "3. Testing get chat rooms...\n";
$roomsResponse = apiRequest('GET', '/chat-rooms', null, $token);

// Test 4: Join a public room (assuming room ID 1 exists)
if (isset($roomsResponse['chat_rooms']) && count($roomsResponse['chat_rooms']) > 0) {
    $roomId = $roomsResponse['chat_rooms'][0]['id'];

    echo "4. Testing join room $roomId...\n";
    apiRequest('POST', "/chat-rooms/$roomId/join", null, $token);

    // Test 5: Send a message
    echo "5. Testing send message...\n";
    apiRequest('POST', "/chat-rooms/$roomId/messages", [
        'content' => 'Hello from API test! Time: ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ], $token);

    // Test 6: Get messages
    echo "6. Testing get messages...\n";
    apiRequest('GET', "/chat-rooms/$roomId/messages", null, $token);

    // Test 7: Create a private room
    echo "7. Testing create private room...\n";
    apiRequest('POST', '/chat-rooms', [
        'name' => 'Test Private Room',
        'type' => 'private',
        'description' => 'A test private room created via API'
    ], $token);
}

// Test 8: Logout
echo "8. Testing logout...\n";
apiRequest('POST', '/auth/logout', null, $token);

echo "=== API Testing Complete ===\n";
