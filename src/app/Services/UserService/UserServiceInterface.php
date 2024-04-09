<?php

namespace App\Services\UserService;

use App\Models\{
    Store,
    User
};

interface UserServiceInterface
{

    /**
     * ユーザーの属するロールがストアダッシュボード配下の操作権限を持っている
     * @param User $user
     * @param Store $store
     * @param int $targetPermissionId
     * @return bool
     */
    public function hasStorePermission(User $user, Store $store, int $targetPermissionId): bool;
}
