<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'store_role_permission')->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_store_role')->withTimestamps();
    }
}
