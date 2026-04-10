<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Fixed admin account — predictable credentials for development
        User::factory()->admin()->create([
            'name'      => 'Admin',
            'email'     => 'admin@example.com',
            'api_token' => Str::random(80),
        ]);

        // Fixed operator account
        User::factory()->operator()->create([
            'name'      => 'Operator',
            'email'     => 'operator@example.com',
            'api_token' => Str::random(80),
        ]);

        // Additional random operators
        User::factory()->operator()->count(3)->create();
    }
}
