<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdRequest;
use Illuminate\Http\Request;
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface,
    BusinessDateRepository\BusinessDateRepositoryInterface,
    UserRepository\UserRepositoryInterface,
};
use App\Repositories\GroupRoleRepository\GroupRoleRepositoryInterface;

class GroupRoleController extends Controller
{
    public function __construct(
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly UserRepositoryInterface $userRepo,
        public readonly GroupRoleRepositoryInterface $groupRoleRepo,
    ) {
    }

    public function index()
    {
        // ログインユーザーのグループIDを取得
        $groupId = auth()->user()->groups->first()->id;

        // デフォルトグループロール一覧を取得
        $groupRoles = $this->groupRoleRepo->getGroupRoles($groupId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'groupRoles' => $groupRoles
            ]
        ], 200);
    }
}
