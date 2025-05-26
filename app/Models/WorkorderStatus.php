<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class workorderStatus extends Model
{
    //
    protected $table = 'workorder_status';

    protected $guarded = ["id"];

    protected $casts = [
        'segments_path' => 'array'
    ];
}
