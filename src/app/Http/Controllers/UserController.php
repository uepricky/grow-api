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
    GroupRepository\GroupRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
    GroupRoleRepository\GroupRoleRepositoryInterface,
};
use App\Services\{
    RoleService\RoleServiceInterface,
    UserService\UserServiceInterface,
};
use App\Http\Requests\StoreIdRequest;
use App\Repositories\StoreRoleRepository\StoreRoleRepositoryInterface;

class UserController extends Controller
{
    public function __construct(
        public readonly UserRepositoryInterface $userRepo,
        public readonly GroupRepositoryInterface $groupRepo,
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly GroupRoleRepositoryInterface $groupRoleRepo,
        public readonly StoreRoleRepositoryInterface $storeRoleRepo,

        public readonly RoleServiceInterface $roleServ,
        public readonly UserServiceInterface $userServ,
    ) {
    }

    public function getLoggedInUser(Request $request)
    {
        $user = $request->user();
        $user['group_id'] =  auth()->user()->groups->first()->id;

        // userの保有しているpermissionsを取得する
        $user['permissions'] = $this->userServ->getUserPermissions($user);

        return $user;
    }

    public function get(int $groupId, int $userId)
    {
        $user = $this->userRepo->find($userId);
        $group = auth()->user()->groups->first();
        $user['group_id'] =  $group->id;

        // 一般ユーザーデータを取得
        $generalUser = $this->userRepo->getGeneralUserData($user);

        // ユーザーのデフォルトグループロール一覧取得
        $groupRoles = $this->groupRoleRepo->getUserGroupRoles($user);
        $filteredGroupRole = $groupRoles->pluck('id');

        // ユーザーのデフォルトストアロール一覧取得
        $userStoreRoles = $this->storeRoleRepo->getUserStoreRoles($user);
        $filteredStoreRoleIds = $userStoreRoles->pluck('id');

        return response()->json([
            'status' => 'success.',
            'data' => [
                'user' => $user,
                'generalUser' => $generalUser,
                'groupRoles' => $filteredGroupRole,
                'storesRoles' => $filteredStoreRoleIds
            ]
        ], 200);
    }

    public function getStoreRoleUsers(int $storeId, int $storeRoleId)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // ストアに属するユーザー一覧を取得
        $storeUsers = $this->userRepo->getStoreUsersByStoreRole($store, $storeRoleId);

        return response()->json([
            'status' => 'success',
            'data' => $storeUsers
        ], 200);
    }

    public function index(int $groupId)
    {
        // ユーザー一覧取得
        $group = auth()->user()->groups->first();
        $usersWithoutGroupRole = $this->userRepo->listGroupUsers($group);
        // $users = $this->userRepo->listGroupUsers($group);

        // グループロール取得しuserに付与
        // TODO: リファクタ
        $users = [];
        foreach ($usersWithoutGroupRole as $userWithoutGroupRole) {
            $userWithoutGroupRole['group_role'] = $this->groupRoleRepo->getUserGroupRoles($userWithoutGroupRole);
            $users[] = $userWithoutGroupRole;
        }

        return response()->json([
            'status' => 'success.',
            'data' => [
                'users' => $users
            ]
        ], 200);
    }

    public function store(UserRequest $request, int $groupId)
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


            $operateUser = $request->user();
            $group = $this->groupRepo->getBelongingGroups($operateUser);
            // ユーザーをグループに所属させる※削除予定
            $this->userRepo->attachToGroup($user, $group);

            // ユーザーとグループロールの紐付け
            $this->userRepo->attachGroupRolesToUser($user, $request->group_roles);

            if (isset($request->stores_roles)) {
                // ユーザーとストアロールの紐付け
                $storeRoleIds = [];
                if ($request->has('stores_roles')) {
                    $storeRoleIds = collect($request->input('stores_roles'))
                        ->flatten()
                        ->all();
                }

                $this->userRepo->attachStoreRolesToUser($user, $storeRoleIds);
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

    public function update(UserRequest $request, int $groupId, int $userId)
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
            if ($request->has('stores_roles')) {
                $storeRoleIds = collect($request->input('stores_roles'))
                    ->flatten()
                    ->all();
            }

            // ストアロールをユーザーに同期する
            $this->userRepo->syncStoreRolesToUser($user, $storeRoleIds);

            // ユーザーとグループロールの紐付け
            $groupRoleIds = [];
            if ($request->has('group_roles')) {
                $groupRoleIds = collect($request->input('group_roles'))
                    ->flatten()
                    ->all();
            }

            // グループロールをユーザーに同期する
            $this->userRepo->syncGroupRolesToUser($user, $groupRoleIds);

            DB::commit();
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバックする
            DB::rollback();

            // ログの出力
            CustomLog::error($e);

            abort(500);
        }
    }

    public function archive(int $groupId, int $userId)
    {
        $user = $this->userRepo->find($userId);
        $this->userRepo->softDeleteUser($user);

        return response()->json([
            'status' => 'success',
            'message' => $user->display_name . 'の削除が完了しました。'
        ], 200);
    }

    public function getUserPermissions(StoreIdRequest $storeIdRequest, int $groupId, int $userId)
    {
        // 契約者
        $user = $this->userRepo->find($userId);
        if (!is_null($user->contractUser)) {
            // getAllPermissionsのようなメソッドで全権限をreturnする
        }

        // グループでの権限


        // ストアでの権限
        if (!is_null($storeIdRequest->storeId)) {
        }
    }
}
