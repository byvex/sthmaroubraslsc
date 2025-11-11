<?php

namespace Database\Seeders;

use App\Enums\ModelStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();

        User::factory()->withPersonalTeam()->create([
            'name' => 'Admin User',
            'username'=> 'admin',
            'role'=> UserRoleEnum::SUPERADMIN,
            'email' => 'admin@example.com',
        ]);

    }
}
