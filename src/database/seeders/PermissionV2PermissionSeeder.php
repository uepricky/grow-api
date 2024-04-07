<?php

namespace Database\Seeders;

use App\Models\PermissionV2Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionV2PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = PermissionV2Permission::PERMISSIONS;
        PermissionV2Permission::insert($permissions);
    }
}
