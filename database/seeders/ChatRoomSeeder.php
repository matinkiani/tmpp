<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChatRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default system user for public rooms
        $systemUser = \App\Models\User::firstOrCreate(
            ['name' => 'System'],
            [
                'email' => 'system@chatapp.local',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        // Create default public chat room
        $publicRoom = \App\Models\ChatRoom::firstOrCreate(
            ['name' => 'General Chat'],
            [
                'type' => 'public',
                'description' => 'Welcome to the general chat room! Feel free to talk about anything.',
                'created_by' => $systemUser->id,
            ]
        );

        // Create another public room
        \App\Models\ChatRoom::firstOrCreate(
            ['name' => 'Random'],
            [
                'type' => 'public',
                'description' => 'Random discussions and fun conversations.',
                'created_by' => $systemUser->id,
            ]
        );
    }
}
