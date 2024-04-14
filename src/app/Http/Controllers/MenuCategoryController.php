<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;
use App\Http\Requests\{
    MenuCategory\MenuCategoryRequest,
    SysMenuCategoryIdRequest
};
use App\Repositories\{
    MenuCategoryRepository\MenuCategoryRepositoryInterface,
    StoreRepository\StoreRepositoryInterface
};
use App\Models\{
    MenuCategory,
    Permission
};
use App\Services\UserService\UserServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;

class MenuCategoryController extends Controller
{
    public function __construct(
        public readonly MenuCategoryRepositoryInterface $menuCategoryRepo,
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly UserServiceInterface $userServ,
    ) {
    }

    public function getAll(SysMenuCategoryIdRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->storeId);

        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // 権限チェック
        $hasPermission = $this->userServ->hasStorePermission(
            $request->user(),
            $store,
            Permission::PERMISSIONS['OPERATION_UNDER_STORE_DASHBOARD']['id']
        );
        if (!$hasPermission) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['この操作を実行する権限がありません']
            ], 403);
        }

        $menuCategories = $this->menuCategoryRepo->getMenuCategoryListByStoreAndSysMenuCategoryIds(
            $store,
            $request->sysMenuCategoryIds
        );

        return response()->json([
            'status' => 'success',
            'data' => $menuCategories
        ], 200);
    }

    public function store(MenuCategoryRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->menu_category['store_id']);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // 新規登録
        $this->menuCategoryRepo->createMenuCategory($request->menu_category);

        return response()->json([
            'status' => 'success'
        ], 200);
    }

    public function get(int $storeId, int $menuCategoryId)
    {
        // メニューカテゴリの取得
        $menuCategory = $this->menuCategoryRepo->find($menuCategoryId);
        if (is_null($menuCategory)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['メニューカテゴリー情報の読み込みができませんでした']
            ], 404);
        }

        // ストアの取得
        $store = $this->storeRepo->findStore($menuCategory->store_id);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $menuCategory
        ], 200);
    }

    public function update(MenuCategoryRequest $request, int $storeId, int $menuCategoryId)
    {
        // メニューカテゴリの取得
        $menuCategory = $this->menuCategoryRepo->find($menuCategoryId);
        if (is_null($menuCategory)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['メニューカテゴリー情報の読み込みができませんでした']
            ], 404);
        }

        // ストアの取得
        $store = $this->storeRepo->findStore($menuCategory->store_id);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            // 現在のレコードを論理削除する
            $this->menuCategoryRepo->softDeleteMenuCategory($menuCategory);

            // 新しいレコードを新規作成する
            $this->menuCategoryRepo->createMenuCategory($request->menu_category);

            DB::commit();
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバックする
            DB::rollback();

            // ログの出力
            CustomLog::error($e);

            return response()->json([
                'status' => 'failure',
                'errors' => [$e->getMessage()]
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => []
        ], 200);
    }

    public function archive(int $storeId, int $menuCategoryId)
    {
        // メニューカテゴリの取得
        $menuCategory = $this->menuCategoryRepo->find($menuCategoryId);
        if (is_null($menuCategory)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['メニューカテゴリー情報の読み込みができませんでした']
            ], 404);
        }

        // ストアの取得
        $store = $this->storeRepo->findStore($menuCategory->store_id);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // レコードを論理削除する
        $this->menuCategoryRepo->softDeleteMenuCategory($menuCategory);

        return response()->json([
            'status' => 'success',
            'data' => []
        ], 200);
    }
}
