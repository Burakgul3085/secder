<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'role' => 'viewer',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@secder.org'],
            [
                'name' => 'SECDER Admin',
                'password' => bcrypt('Secder123!'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->call([
            DemoContentSeeder::class,
            CrmSeeder::class,
        ]);
    }
}
