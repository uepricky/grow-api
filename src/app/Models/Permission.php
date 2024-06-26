<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'description'
    ];

    const PERMISSIONS = [
        "OPERATION_UNDER_GROUP_DASHBOARD" => ['id' => 1, 'description' => 'グループダッシュボード配下の操作ができる'],
        "OPERATION_UNDER_STORE_DASHBOARD" => ['id' => 2, 'description' => 'ストアダッシュボード配下の操作ができる'],
    ];

    public function groupRoles()
    {
        return $this->belongsToMany(GroupRole::class, 'group_role_permission');
    }

    public function storeRoles()
    {
        return $this->belongsToMany(StoreRole::class, 'store_role_permission');
    }
}
