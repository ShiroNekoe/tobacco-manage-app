<?php

namespace App\Policies;

use App\Models\ProductionRun;
use App\Models\User;

class ProductionRunPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProductionRun $productionRun): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Warehouse cannot submit production runs; operators, group leaders, supervisors, admins can.
        return ! $user->isWarehouse();
    }

    public function update(User $user, ProductionRun $productionRun): bool
    {
        if ($productionRun->isLocked()) {
            return false;
        }

        if ($user->isWarehouse() || $user->isProductionManager()) {
            return false;
        }

        return true;
    }

    public function unlock(User $user, ProductionRun $productionRun): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }
}
