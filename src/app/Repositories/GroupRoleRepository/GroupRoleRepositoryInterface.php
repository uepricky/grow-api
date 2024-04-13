<?php

namespace App\Repositories\GroupRoleRepository;

use Illuminate\Database\Eloquent\Collection;
use App\Models\{
    GroupRole,
    User
};

interface GroupRoleRepositoryInterface
{
    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * @param array $data
     *
     * @return GroupRole
     */
    public function createGroupRole(array $data): GroupRole;

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * ユーザーの属するロール一覧を取得する
     * @param User $user
     * @return Collection
     */
    public function getUserGroupRoles(User $user): Collection;

    /**
     * グループロール一覧を取得する
     * @param int $groupId
     * @return Collection
     */
    public function getGroupRoles(int $groupId): Collection;

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
     *  @param GroupRole $groupRole
     *  @param array $permissionIds
     */
    public function attachPermissionsToGroupRole(GroupRole $groupRole, array $permissionIds): void;
}
