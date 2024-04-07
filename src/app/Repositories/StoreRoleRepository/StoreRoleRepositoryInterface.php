<?php

namespace App\Repositories\StoreRoleRepository;

use Illuminate\Database\Eloquent\Collection;
use App\Models\{
    PermissionV2StoreRole
};

interface StoreRoleRepositoryInterface
{
    /***********************************************************
    * Create系
    ***********************************************************/
    /**
     * @param array $data
     *
     * @return PermissionV2StoreRole
     */
    public function createStoreRole(array $data): PermissionV2StoreRole;

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * @param int $storeId
     * @param string $storeRoleName
     * @return PermissionV2StoreRole
     */
    public function getStoreRoleByName(int $storeId, string $storeRoleName): PermissionV2StoreRole;

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
     *  @param PermissionV2StoreRole $StoreRole
     *  @param array $permissionIds
     */
    public function attachPermissionsToStoreRole(PermissionV2StoreRole $StoreRole, array $permissionIds): void;
}
