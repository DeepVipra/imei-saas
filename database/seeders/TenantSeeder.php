<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::firstOrCreate(
            ['name' => 'Default Tenant'],
            [
                'subscription_plan' => 'basic',
                'imei_limit' => 500000
            ]
        );
    }
}
