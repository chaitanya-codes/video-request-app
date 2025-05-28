<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderStatus extends Model
{
    protected $table = 'workorder_status';

    protected $guarded = ["id"];

    protected $casts = [
        'segments_path' => 'array'
    ];
}
