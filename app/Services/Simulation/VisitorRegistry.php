<?php

namespace App\Services\Simulation;

use App\Models\User;
use App\Models\Visitor;

class VisitorRegistry
{
    public function forUser(User $user): ?Visitor
    {
        if ($user->role !== 'customer') {
            return null;
        }

        return Visitor::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $user->name, 'email' => $user->email]
        );
    }
}
