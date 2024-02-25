<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreIdRequest;
use App\Models\{
    Store,
    SysMenuCategory
};
use App\Repositories\{
    TableRepository\TableRepositoryInterface,
    MenuRepository\MenuRepositoryInterface,
    BusinessDateRepository\BusinessDateRepositoryInterface,
    ItemizedSetOrderRepository\ItemizedSetOrderRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};
use App\Services\{
    BillService\BillServiceInterface,
};
use App\ViewModels\Hall\HallIndexViewModel;

class HallController extends Controller
{
    public function __construct(
        public readonly TableRepositoryInterface $tableRepo,
        public readonly MenuRepositoryInterface $menuRepo,
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly ItemizedSetOrderRepositoryInterface $itemizedSetOrderRepo,
        public readonly StoreRepositoryInterface $storeRepo,

        public readonly BillServiceInterface $billServ,
    ) {}

    public function get(StoreIdRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // テーブル一覧取得(伝票情報含む)
        $tables = $this->billServ->getAllDisplayTablesWithStatus($store);

        // 店舗に紐づく初回セットを取得
        $firstSets = $this->menuRepo->getDisplayMenuListByStoreAndSysMenuCategoryIds($store, SysMenuCategory::CATEGORIES['FIRST_SET']['id']);

        // 営業日を取得
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);

        // 店舗に紐づく延長セットを取得
        $extensionSets = $this->menuRepo->getDisplayMenuListByStoreAndSysMenuCategoryIds($store, SysMenuCategory::CATEGORIES['EXTENSION_SET']['id']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'store' => $store,
                'businessDate' => $businessDate,
                'tables' => $tables,
                'firstSets' => $firstSets,
                'extensionSets' => $extensionSets
            ]
        ], 200);
    }

    public function index(Store $store)
    {
        // Policy確認

        // テーブル一覧取得(伝票情報含む)
        $tables = $this->billServ->getAllDisplayTablesWithStatus($store);

        // 店舗に紐づく初回セットを取得
        $firstSets = $this->menuRepo->getDisplayMenuListByStoreAndSysMenuCategoryIds($store, SysMenuCategory::CATEGORIES['FIRST_SET']['id']);

        // 営業日を取得
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);

        // 店舗に紐づく延長セットを取得
        $extensionSets = $this->menuRepo->getDisplayMenuListByStoreAndSysMenuCategoryIds($store, SysMenuCategory::CATEGORIES['EXTENSION_SET']['id']);

        // ViewModel
        $viewModel = new HallIndexViewModel(
            $store,
            $businessDate,
            $tables,
            $firstSets,
            $extensionSets,
            $this->itemizedSetOrderRepo
        );

        return view('hall.index', compact('viewModel'));
    }
}
