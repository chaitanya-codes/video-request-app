<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $table = 'video_requests';

    protected $guarded = ["id"];

    public function status()
    {
        return $this->hasOne(WorkorderStatus::class, 'video_request_id', 'id');
    }
}
