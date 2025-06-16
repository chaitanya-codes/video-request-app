<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkorderFile extends Model
{
    use HasFactory;
    protected $table = 'workorder_files';
    protected $guarded = ["id"];
}
