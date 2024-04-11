<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionV2StoreRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
    ];

    public function permissions()
    {
        return $this->belongsToMany(PermissionV2Permission::class, 'permission_v2_store_role_permission')->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_v2_user_store_role')->withTimestamps();
    }
}
