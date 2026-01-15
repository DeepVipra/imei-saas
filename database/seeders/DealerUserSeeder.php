<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Dealer;
use Illuminate\Support\Facades\Hash;

class DealerUserSeeder extends Seeder
{
    public function run(): void
    {
        $dealer = Dealer::where('name', 'Test Dealer')->first();

        User::firstOrCreate(
            ['email' => 'dealer@imei.test'],
            [
                'name'      => 'Test Dealer User',
                'password'  => Hash::make('Dealer@123'),
                'tenant_id' => $dealer->tenant_id,
                'dealer_id' => $dealer->id,
                'role'      => 'dealer',
            ]
        );
    }
}
