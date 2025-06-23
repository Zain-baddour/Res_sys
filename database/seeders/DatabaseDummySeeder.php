<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 2 owners
        User::factory()->create([
            'name' => 'Owner One',
            'email' => 'owner1@example.com',
            'role' => 'owner'
        ]);

        User::factory()->create([
            'name' => 'Owner Two',
            'email' => 'owner2@example.com',
            'role' => 'owner'
        ]);

        // 2 assistants
        User::factory()->create([
            'name' => 'Assistant One',
            'email' => 'assistant1@example.com',
            'role' => 'assistant'
        ]);

        User::factory()->create([
            'name' => 'Assistant Two',
            'email' => 'assistant2@example.com',
            'role' => 'assistant'
        ]);

        // 2 clients
        User::factory()->create([
            'name' => 'Client One',
            'email' => 'client1@example.com',
            'role' => 'client'
        ]);

        User::factory()->create([
            'name' => 'Client Two',
            'email' => 'client2@example.com',
            'role' => 'client'
        ]);
    }
}
