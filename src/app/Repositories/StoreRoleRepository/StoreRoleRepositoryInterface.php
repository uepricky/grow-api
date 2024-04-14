<?php

namespace App\Repositories\StoreRoleRepository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\{
    StoreRole,
    User,
    Store
};

interface StoreRoleRepositoryInterface
{
    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * @param array $data
     *
     * @return StoreRole
     */
    public function createStoreRole(array $data): StoreRole;

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * @param int $storeId
     * @param string $storeRoleName
     * @return StoreRole
     */
    public function getStoreRoleByName(int $storeId, string $storeRoleName): StoreRole;

    /**
     * ストアロール一覧を取得する
     * @param int $storeId
     * @return Collection
     */
    public function getStoreRoles(int $storeId): Collection;

    /**
     * ユーザーに紐づくストアロール一覧を取得する
     * @param User $user
     * @return Collection
     */
    public function getUserStoreRoles(User $user): Collection;

    /**
     * ユーザーの指定された店舗のロール一覧を取得する
     * @param Authenticatable $user
     * @param Store $store
     * @return Collection
     */
    public function getUserStoreStoreRoles(Authenticatable $user, Store $store): Collection;

    /**
     * ストアロールIDの保有するパーミッション一覧を取得する
     * @param int $id
     * @return Collection
     */
    public function getStoreRolePermissions(int $id): Collection;

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
     *  @param StoreRole $StoreRole
     *  @param array $permissionIds
     */
    public function attachPermissionsToStoreRole(StoreRole $StoreRole, array $permissionIds): void;
}
