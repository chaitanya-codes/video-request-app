<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderStatus extends Model
{
    protected $table = 'work_order_statuses';

    protected $guarded = ["id"];

    protected $casts = [
        'segments_path' => 'array'
    ];
}
