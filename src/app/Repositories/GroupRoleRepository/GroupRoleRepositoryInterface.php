<?php

namespace App\Repositories\GroupRoleRepository;

use Illuminate\Database\Eloquent\Collection;
use App\Models\{
    PermissionV2GroupRole
};

interface GroupRoleRepositoryInterface
{
    /***********************************************************
    * Create系
    ***********************************************************/
    /**
     * @param array $data
     *
     * @return PermissionV2GroupRole
     */
    public function createGroupRole(array $data): PermissionV2GroupRole;

    /***********************************************************
     * Read系
     ***********************************************************/

    /***********************************************************
     * Update系
     ***********************************************************/

    /***********************************************************
     * Delete系
     ***********************************************************/

    /***********************************************************
     * その他
     ***********************************************************/
    /**
     *　権限をグループロールにアタッチする
     *  @param PermissionV2GroupRole $groupRole
     *  @param array $permissionIds
     */
    public function attachPermissionsToGroupRole(PermissionV2GroupRole $groupRole, array $permissionIds): void;
}
