<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class permissionV2Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'description'
    ];
}
