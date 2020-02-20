<?php

namespace App\Policies;

use App\Rapport;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RapportPolicy
{
    use HandlesAuthorization;

    private function hasCustomerAccessToRapport(User $user, Rapport $rapport)
    {
        return $user->customer && $user->customer->id === $rapport->customer_id;
    }

    public function before($user, $ability)
    {
        if ($user->isType('superadmin')) return true;
    }

    public function view(User $user, Rapport $rapport)
    {
        if ($user->hasRule(['rapport_read'])) return true;
        return $this->hasCustomerAccessToRapport($user, $rapport);
    }

    public function create(User $user)
    {
        return $user->hasRule(['rapport_write']);
    }

    public function update(User $user, Rapport $rapport)
    {
        if ($user->hasRule(['rapport_write'])) return true;
        return $this->hasCustomerAccessToRapport($user, $rapport);
    }

    public function delete(User $user)
    {
        return $user->hasRule(['rapport_write']);
    }

    public function restore(User $user)
    {
        return $user->hasRule(['rapport_write']);
    }

    public function forceDelete(User $user)
    {
        return $user->hasRule(['rapport_write']);
    }
}
