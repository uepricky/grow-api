<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\{
    UserRepository\UserRepositoryInterface,
    RoleRepository\RoleRepositoryInterface,
};

class UserController extends Controller
{
    public function __construct(
        public readonly UserRepositoryInterface $userRepo,
        public readonly RoleRepositoryInterface $roleRepo,
    ) {
    }

    public function get(Request $request)
    {
        return $request->user();
    }

    public function index()
    {
        // ユーザー一覧取得
        $group = auth()->user()->groups->first();
        $usersWithoutGroupRole = $this->userRepo->listGroupUsers($group);

        // グループロール取得しuserに付与
        $users = [];
        foreach ($usersWithoutGroupRole as $userWithoutGroupRole) {
            $userWithoutGroupRole['group_role'] = $this->roleRepo->getUserGroupRole($userWithoutGroupRole);
            $users[] = $userWithoutGroupRole;
        }

        return response()->json([
            'status' => 'success.',
            'data' => [
                'users' => $users
            ]
        ], 200);
    }

    public function archive(int $userId)
    {
        $user = $this->userRepo->find($userId);
        $this->userRepo->softDeleteUser($user);

        return response()->json([
            'status' => 'success',
            'message' => $user->display_name . 'の削除が完了しました。'
        ], 200);
    }
}
