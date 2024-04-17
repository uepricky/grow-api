<?php

namespace App\Repositories\GroupRoleRepository;

use Illuminate\Database\Eloquent\Collection;
use App\Models\{
    GroupRole,
    User
};


class GroupRoleRepository implements GroupRoleRepositoryInterface
{
    public function __construct(GroupRole $model)
    {
        $this->model = $model;
    }

    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * @param array $data
     *
     * @return GroupRole
     */
    public function createGroupRole(array $data): GroupRole
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
        return $user->GroupRoles;
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

    /**
     * 指定されたnameのグループロールを取得する
     * @param int $groupId
     * @param string $groupRoleName
     * @return GroupRole
     */
    public function getGroupRoleByName(int $groupId, string $groupRoleName): GroupRole
    {
        return $this->model->where('group_id', $groupId)->where('name', $groupRoleName)->first();
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
     *  @param GroupRole $groupRole
     *  @param array $permissionIds
     */
    public function attachPermissionsToGroupRole(GroupRole $groupRole, array $permissionIds): void
    {
        $groupRole->permissions()->attach($permissionIds);
    }
}
