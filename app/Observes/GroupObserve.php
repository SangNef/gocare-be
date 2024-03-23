<?php

namespace App\Observes;

use App\Models\Group;

class GroupObserve
{
    public function saving(Group $group)
    {
        $group->name = preg_replace('/\s+/', '_', strtolower($group->name));
    }
}
