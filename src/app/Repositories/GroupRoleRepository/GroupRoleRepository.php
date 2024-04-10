<?php

namespace App\Repositories\GroupRoleRepository;

use Illuminate\Database\Eloquent\Collection;
use App\Models\{
    PermissionV2GroupRole,
    User
};


class GroupRoleRepository implements GroupRoleRepositoryInterface
{
    public function __construct(PermissionV2GroupRole $model)
    {
        $this->model = $model;
    }

    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * @param array $data
     *
     * @return PermissionV2GroupRole
     */
    public function createGroupRole(array $data): PermissionV2GroupRole
    {
        return $this->model->create($data);
    }

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * ユーザーの属するロール一覧を取得する
     * @param User $user
     * @return Collection
     */
    public function getUserGroupRoles(User $user): Collection
    {
        return $user->permissionV2GroupRoles;
    }

    /**
     * グループロール一覧を取得する
     * @param int $groupId
     * @return Collection
     */
    public function getGroupRoles(int $groupId): Collection
    {
        return $this->model->where('group_id', $groupId)->get();
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
     *  @param PermissionV2GroupRole $groupRole
     *  @param array $permissionIds
     */
    public function attachPermissionsToGroupRole(PermissionV2GroupRole $groupRole, array $permissionIds): void
    {
        $groupRole->permissions()->attach($permissionIds);
    }
}
