<?php

namespace Database\Seeders;

use App\Models\hall;
use App\Models\Servicetohall;
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
        $owner1 = User::factory()->create([
            'name' => 'Owner One',
            'email' => 'owner1@example.com',
            'role' => 'owner'
        ]);

        $owner2 = User::factory()->create([
            'name' => 'Owner Two',
            'email' => 'owner2@example.com',
            'role' => 'owner'
        ]);

        // 2 assistants
        $assistant1 = User::factory()->create([
            'name' => 'Assistant One',
            'email' => 'assistant1@example.com',
            'role' => 'assistant'
        ]);

        $assistant2 = User::factory()->create([
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

        // ومنولد الصالات
        $hall1 = hall::create([
            'name' => 'Hall One',
            'hall_image' => null,  // أو fake()->imageUrl() لو بدك صورة وهمية
            'owner_id' => $owner1->id,
            'location' => 'damascus',
            'capacity' => 300,
            'contact' => '0999888777',
            'type' => 'joys',
            'events' => 'wedding,birthday',
            'status' => 'approved',
        ]);

        $hall2 = hall::create([
            'name' => 'Hall Two',
            'hall_image' => null,
            'owner_id' => $owner2->id,
            'location' => 'Latakia',
            'capacity' => 200,
            'contact' => '0988777666',
            'type' => 'sorrows',
            'events' => 'funeral',
            'status' => 'approved',
        ]);

        $hall1->employees()->attach($assistant1->id);

        $service1 = Servicetohall::create([
            'hall_id' => $hall1->id,
            'name' => 'performance_service',
            'service_price' => 20.0,
            'description' => 'arada service',
            'is_fixed' => true,
        ]);
    }
}
