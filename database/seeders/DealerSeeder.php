<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dealer;
use App\Models\Tenant;

class DealerSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        Dealer::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Test Dealer'
            ]
        );
    }
}
