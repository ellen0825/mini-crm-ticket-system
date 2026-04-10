<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Fixed admin — predictable credentials for development
        $admin = User::factory()->create([
            'name'      => 'Admin',
            'email'     => 'admin@example.com',
            'api_token' => Str::random(80),
        ]);
        $admin->assignRole('admin');

        // Fixed operator
        $operator = User::factory()->create([
            'name'      => 'Operator',
            'email'     => 'operator@example.com',
            'api_token' => Str::random(80),
        ]);
        $operator->assignRole('operator');

        // Additional random operators
        User::factory()->count(3)->create()->each(
            fn (User $u) => $u->assignRole('operator')
        );
    }
}
