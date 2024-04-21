<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'group_role_permission')->withTimestamps();
    }
}
