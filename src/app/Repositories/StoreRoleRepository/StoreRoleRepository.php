<?php

namespace App\Repositories\StoreRoleRepository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\{
    PermissionV2StoreRole,
    User,
    Store
};


class StoreRoleRepository implements StoreRoleRepositoryInterface
{
    public function __construct(PermissionV2StoreRole $model)
    {
        $this->model = $model;
    }

    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * @param array $data
     *
     * @return PermissionV2StoreRole
     */
    public function createStoreRole(array $data): PermissionV2StoreRole
    {
        return $this->model->create($data);
    }

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * @param int $storeId
     * @param string $storeRoleName
     * @return PermissionV2StoreRole
     */
    public function getStoreRoleByName(int $storeId, string $storeRoleName): PermissionV2StoreRole
    {
        return $this->model->where('store_id', $storeId)->where('name', $storeRoleName)->first();
    }

    /**
     * ストアロール一覧を取得する
     * @param int $storeId
     * @return Collection
     */
    public function getStoreRoles(int $storeId): Collection
    {
        return $this->model->where('store_id', $storeId)->get();
    }

    /**
     * ユーザーに紐づくストアロール一覧を取得する
     * @param User $user
     * @return Collection
     */
    public function getUserStoreRoles(User $user): Collection
    {
        return $user->permissionV2StoreRoles;
    }

    /**
     * ユーザーの指定された店舗のロール一覧を取得する
     * @param Authenticatable $user
     * @param Store $store
     * @return Collection
     */
    public function getUserStoreStoreRoles(Authenticatable $user, Store $store): Collection
    {
        return $this->getUserStoreRoles($user)->where('store_id', $store->id);
    }

    /**
     * ストアロールIDの保有するパーミッション一覧を取得する
     * @param int $id
     * @return Collection
     */
    public function getStoreRolePermissions(int $id): Collection
    {
        return $this->model->findOrFail($id)->permissions;
    }

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
    public function attachPermissionsToStoreRole(PermissionV2StoreRole $StoreRole, array $permissionIds): void
    {
        $StoreRole->permissions()->attach($permissionIds);
    }
}
