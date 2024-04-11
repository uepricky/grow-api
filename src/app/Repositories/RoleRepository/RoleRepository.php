<?php

namespace App\Repositories\RoleRepository;

use Illuminate\Support\Collection;
use App\Repositories\RoleRepository\RoleRepositoryInterface;
use App\Models\{
    Role,
    Group,
    User,
    Store,
    DefaultGroupRole,
    DefaultStoreRole,
    GroupRole,
    StoreRole,
};


class RoleRepository implements RoleRepositoryInterface
{
    public function __construct(Role $role)
    {
        $this->model = $role;
    }

    /***********************************************************
     * Create系
     ***********************************************************/

    /***********************************************************
     * Read系
     ***********************************************************/

    /**
     * ユーザーの属するストアロールを取得
     * @param User $user
     * @param int $storeId
     * @return Collection
     */
    public function getUserStoreRoles(User $user, int $storeId): Collection
    {
        return $user->storeRoles()
            ->where('store_id', $storeId)
            ->with('role')
            ->get();
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
}
