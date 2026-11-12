<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Administrators are never seeded with fixed credentials: create one
     * interactively with "php artisan kliq:admin".
     */
    public function run(): void
    {
        $this->call(ContentSeeder::class);

        if (! User::query()->where('is_admin', true)->exists()) {
            $this->command?->warn('No administrator yet. Create one with: php artisan kliq:admin');
        }
    }
}
