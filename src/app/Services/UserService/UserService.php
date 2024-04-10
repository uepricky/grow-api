<?php

namespace App\Services\UserService;

use App\Services\UserService\UserServiceInterface;
use App\Models\{
    Store,
    User
};
use App\Repositories\{
    StoreRoleRepository\StoreRoleRepositoryInterface,
};

class UserService implements UserServiceInterface
{
    public function __construct(
        public readonly StoreRoleRepositoryInterface $storeRoleRepo,
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
}
