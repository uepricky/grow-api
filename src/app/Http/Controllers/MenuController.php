<?php

namespace App\Http\Controllers;

use App\Http\Requests\Menu\MenuRequest;
use App\Http\Requests\StoreIdRequest;
use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;
use Illuminate\Auth\Access\AuthorizationException;
use App\Repositories\{
    MenuCategoryRepository\MenuCategoryRepositoryInterface,
    MenuRepository\MenuRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};
use App\Models\{
    Menu,
    SysMenuCategory,
    PermissionV2Permission
};
use App\Services\UserService\UserServiceInterface;

class MenuController extends Controller
{
    public function __construct(
        public readonly MenuCategoryRepositoryInterface $menuCategoryRepo,
        public readonly MenuRepositoryInterface $menuRepo,
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly UserServiceInterface $userServ,
    ) {
    }

    public function getAll(StoreIdRequest $request)
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
            PermissionV2Permission::PERMISSIONS['OPERATION_UNDER_STORE_DASHBOARD']['id']
        );
        if (!$hasPermission) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['この操作を実行する権限がありません']
            ], 403);
        }

        // メニューカテゴリを取得
        $menus = $this->menuRepo->getMenuListByStoreAndSysMenuCategoryIds($store, SysMenuCategory::CATEGORIES['DRINK_FOOD']['id']);

        return response()->json([
            'status' => 'success',
            'data' => $menus
        ], 200);
    }

    public function store(MenuRequest $request)
    {
        // メニューカテゴリの取得
        $menuCategory = $this->menuCategoryRepo->find($request->menu['menu_category_id']);
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

        $this->menuRepo->createMenu($request->menu);

        return response()->json([
            'status' => 'success',
            'data' => []
        ], 200);
    }

    public function get(int $id)
    {
        // メニューの取得
        $menu = $this->menuRepo->find($id);
        if (is_null($menu)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['メニュー情報の読み込みができませんでした']
            ], 404);
        }

        // メニューカテゴリの取得
        $menuCategory = $this->menuCategoryRepo->find($menu->menu_category_id);
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

        $menu->store_id = $store->id;

        return response()->json([
            'status' => 'success',
            'data' => $menu
        ], 200);
    }

    public function update(MenuRequest $request, int $id)
    {
        // メニューの取得
        $menu = $this->menuRepo->find($id);
        if (is_null($menu)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['メニュー情報の読み込みができませんでした']
            ], 404);
        }

        // メニューカテゴリの取得
        $menuCategory = $this->menuCategoryRepo->find($menu->menu_category_id);
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
            $this->menuRepo->softDeleteMenu($menu);

            // 新しいレコードを新規作成する
            $this->menuRepo->createMenu($request->menu);

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

    public function archive(int $id)
    {
        // メニューの取得
        $menu = $this->menuRepo->find($id);
        if (is_null($menu)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['メニュー情報の読み込みができませんでした']
            ], 404);
        }

        // メニューカテゴリの取得
        $menuCategory = $this->menuCategoryRepo->find($menu->menu_category_id);
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
        $this->menuRepo->softDeleteMenu($menu);

        return response()->json([
            'status' => 'success',
            'data' => []
        ], 200);
    }
}
