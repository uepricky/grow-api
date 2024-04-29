<?php

namespace App\Services\UserService;

use App\Services\UserService\UserServiceInterface;
use App\Models\{
    Store,
    User
};
use App\Repositories\{
    StoreRoleRepository\StoreRoleRepositoryInterface,
    GroupRoleRepository\GroupRoleRepositoryInterface,
    UserRepository\UserRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};

class UserService implements UserServiceInterface
{
    public function __construct(
        public readonly StoreRoleRepositoryInterface $storeRoleRepo,
        public readonly GroupRoleRepositoryInterface $groupRoleRepo,
        public readonly UserRepositoryInterface $userRepo,
        public readonly StoreRepositoryInterface $storeRepo,
    ) {
    }

    /**
     * ユーザーの属するロールがストアダッシュボード配下の操作権限を持っている
     * @param User $user
     * @param Store $store
     * @param int $targetPermissionId
     * @return bool
     */
    public function hasStorePermission(User $user, Store $store, int $targetPermissionId): bool
    {
        // ユーザーの指定された店舗のロール一覧を取得する
        $userStoreRoles = $this->storeRoleRepo->getUserStoreStoreRoles($user, $store);

        // ロールの保有するパーミッションを取得する
        foreach ($userStoreRoles as $userStoreRole) {
            $permissions = $this->storeRoleRepo->getStoreRolePermissions($userStoreRole->id);
            foreach ($permissions as $permission) {
                if ($permission->id === $targetPermissionId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ユーザーの属するグループロールが指定の操作権限を持っている
     * @param User $user
     * @param int $targetPermissionId
     * @return bool
     */
    public function hasGroupPermission(User $user, int $targetPermissionId): bool
    {
        // ユーザーの指定されたグループロール一覧を取得する
        $userGroupRoles = $this->groupRoleRepo->getUserGroupRoles($user);

        // ロールの保有するパーミッションを取得する
        foreach ($userGroupRoles as $userGroupRole) {
            $permissions = $this->groupRoleRepo->getGroupRolePermissions($userGroupRole->id);
            foreach ($permissions as $permission) {
                if ($permission->id === $targetPermissionId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ユーザーの保有するパーミッション一覧を取得する
     * @param User $user
     * @return array
     */
    public function getUserPermissions(User $user): array
    {
        // グループ権限を取得
        $groupPermissions = [];

        $userGroupRoles = $this->groupRoleRepo->getUserGroupRoles($user);
        // ロールの保有するパーミッションを取得する
        foreach ($userGroupRoles as $userGroupRole) {
            $groupPermissions = $this->groupRoleRepo->getGroupRolePermissions($userGroupRole->id)->pluck('id');
        }

        // 各所属店舗のストア権限を取得
        $storesPermissions = [];

        // グループに属する店舗一覧を取得する
        $stores = $this->storeRepo->getStoreListByGroup($user->groups->first());

        foreach ($stores as $store) {
            // ユーザーの店舗ロールを取得する
            $userStoreRoles = $this->storeRoleRepo->getUserStoreStoreRoles($user, $store);
            $storesPermissions[$store->id] = [];
            // 店舗ロールの保有する権限を取得する
            foreach ($userStoreRoles as $userStoreRole) {
                $rolePermissions = $this->storeRoleRepo->getStoreRolePermissions($userStoreRole->id)->pluck('id');
                $storesPermissions[$store->id] = array_merge($storesPermissions[$store->id], $rolePermissions->toArray());
            }
        }

        return
            [
                'group' => $groupPermissions,
                'stores' => $storesPermissions
            ];
    }
}
