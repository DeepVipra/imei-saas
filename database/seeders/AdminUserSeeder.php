<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        User::firstOrCreate(
            ['email' => 'admin@imei.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'tenant_id' => $tenant->id,
                'role' => 'admin',
            ]
        );
    }
}
