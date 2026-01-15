<?php

namespace App\Policies;

use App\Models\User;

class DevicePolicy
{
    /**
     * Create a new policy instance.
     */
    public function view(User $user, Device $device)
    {
        if ($user->role === 'dealer') {
        return $device->allocation?->dealer_id === $user->dealer_id;
    }
        return true;
    }
}