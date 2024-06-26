<?php

namespace App\Repositories\GroupRepository;

use Illuminate\Database\Eloquent\Collection;
use App\Models\{
    Group,
    User
};

interface GroupRepositoryInterface
{
    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * グループを作成する
     *
     * @param array $data グループ作成に必要なデータ
     * @return Group 作成されたグループ
     */
    public function createGroup(array $data): Group;

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * @param int $groupId
     * @return Group
     */
    public function findGroup(int $groupId): Group;

    /**
     * ユーザーの所属グループを取得
     * @param User $user
     * @return Group
     */
    public function getBelongingGroups(User $user): Group;

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
