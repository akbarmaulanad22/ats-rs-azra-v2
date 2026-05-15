<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => Role::HrAdmin,
            'must_change_password' => true,
        ]);

        $this->call([
            StageSeeder::class,
            WorkflowTemplateSeeder::class,
            UnitSeeder::class,
            EmailTemplateSeeder::class,
        ]);
    }
}
