<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Log\CustomLog;
use App\Http\Requests\User\UserRequest;
use Illuminate\Http\Request;
use App\Repositories\{
    UserRepository\UserRepositoryInterface,
    RoleRepository\RoleRepositoryInterface,
    GroupRepository\GroupRepositoryInterface,
};
use App\Services\{
    RoleService\RoleServiceInterface
};

class UserController extends Controller
{
    public function __construct(
        public readonly UserRepositoryInterface $userRepo,
        public readonly RoleRepositoryInterface $roleRepo,
        public readonly GroupRepositoryInterface $groupRepo,
        public readonly RoleServiceInterface $roleServ,
    ) {
    }

    public function getLoggedInUser(Request $request)
    {
        $user = $request->user();
        $user['group_id'] =  auth()->user()->groups->first()->id;
        return $user;
    }

    public function get(int $userId)
    {
        $user = $this->userRepo->find($userId);
        $group = auth()->user()->groups->first();
        $user['group_id'] =  $group->id;

        // 一般ユーザーデータを取得
        $generalUser = $this->userRepo->getGeneralUserData($user);

        // ユーザーのデフォルトグループロール一覧取得
        $groupRole = $this->roleRepo->getUserGroupRole($user);
        $filteredGroupRole = $groupRole->pivot->group_role_id;

        // ユーザーのデフォルトストアロール一覧取得
        $userStoreRoles = $this->roleRepo->getUserStoresRoles($user);
        $filteredStoreRoleIds = $userStoreRoles->pluck('pivot.store_role_id');

        return response()->json([
            'status' => 'success.',
            'data' => [
                'user' => $user,
                'generalUser' => $generalUser,
                'groupRole' => $filteredGroupRole,
                'storesRoles' => $filteredStoreRoleIds
            ]
        ], 200);
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

    public function store(UserRequest $request)
    {
        // トランザクションを開始する
        DB::beginTransaction();
        try {
            // ユーザーの新規登録
            $data = $request->user;
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = $this->userRepo->createGeneralUser($data, $request->general_user);

            // ユーザーをグループに所属させる
            $operateUser = $request->user();
            $group = $this->groupRepo->getBelongingGroups($operateUser);
            $this->userRepo->attachToGroup($user, $group);

            // ユーザーとグループロールの紐付け
            $this->roleRepo->attachGroupRolesToUser($user, [$request->group_role]);

            if (isset($request->store_role)) {
                // ユーザーを店舗に所属させる
                $storeIds = array_keys($request->input('store_role'));
                $this->userRepo->attachToStores($user, $storeIds);

                // ユーザーとストアロールの紐付け
                $storeRoleIds = [];
                if ($request->has('store_role')) {
                    $storeRoleIds = collect($request->input('store_role'))
                        ->flatten()
                        ->all();
                }

                $this->roleRepo->attachStoreRolesToUser($user, $storeRoleIds);
            }


            DB::commit();
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバックする
            DB::rollback();
            // ログの出力
            CustomLog::error($e);

            abort(500);
        }

        return response()->json([
            'status' => 'success',
            'message' => '登録が完了しました。',
            'data' => [
                'user' => $user
            ]
        ], 200);
    }

    public function update(UserRequest $request, int $userId)
    {
        // トランザクションを開始する
        DB::beginTransaction();

        try {
            $user = $this->userRepo->find($userId);

            // ユーザーの編集
            $data = $request->user;
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            $this->userRepo->updateGeneralUser($user, $data, $request->general_user);

            // ユーザーとストアロールの紐付け
            $storeRoleIds = [];
            if ($request->has('store_role')) {
                $storeRoleIds = collect($request->input('store_role'))
                    ->flatten()
                    ->all();
            }

            // グループロールをユーザーに同期する
            $this->roleRepo->syncGroupRoleUser($user, [$request->group_role]);

            // ストアロールをユーザーに同期する
            $this->roleRepo->syncStoreRoleUser($user, $storeRoleIds);

            DB::commit();
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバックする
            DB::rollback();

            // ログの出力
            CustomLog::error($e);

            abort(500);
        }
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
