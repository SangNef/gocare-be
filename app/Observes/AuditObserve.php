<?php
namespace App\Observes;

use App\Models\Audit;

class AuditObserve
{
    public function saving(Audit $audit) {
        $audit->store_id = $audit->customer->store_id;
    }
}