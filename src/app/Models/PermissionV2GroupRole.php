<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionV2GroupRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
    ];

    const DEFAULT_GROUP_ROLES = [
        "ADMIN" => ['name' => '管理者']
    ];

    public function permissions()
    {
        return $this->belongsToMany(PermissionV2Permission::class, 'permission_v2_group_role_permission')->withTimestamps();
    }
}
