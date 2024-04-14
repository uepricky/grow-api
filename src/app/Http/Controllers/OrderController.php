<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\UpdateOrderRequest;
use Illuminate\Http\Request;
use App\Models\{
    Store,
    Bill,
    SysMenuCategory,
    UserIncentive,
    Order,
    Digit,
    RoundingMethod,
    SysPaymentMethodCategory,
    BillPayment
};
use App\Repositories\{
    BillRepository\BillRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,

    MenuCategoryRepository\MenuCategoryRepositoryInterface,
    MenuRepository\MenuRepositoryInterface,
    UserRepository\UserRepositoryInterface,
    BusinessDateRepository\BusinessDateRepositoryInterface,
    TableRepository\TableRepositoryInterface,
    ItemizedOrderRepository\ItemizedOrderRepositoryInterface,
    OrderRepository\OrderRepositoryInterface,
    StoreDetailRepository\StoreDetailRepositoryInterface,
    PaymentMethodRepository\PaymentMethodRepositoryInterface,
    NumberOfCustomerRepository\NumberOfCustomerRepositoryInterface,
    StoreRoleRepository\StoreRoleRepositoryInterface,
};
use App\Services\{
    BillService\BillServiceInterface,
    OrderService\OrderServiceInterface,
};
use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;

class OrderController extends Controller
{
    public function __construct(
        public readonly BillRepositoryInterface $billRepo,
        public readonly StoreRepositoryInterface $storeRepo,

        public readonly MenuCategoryRepositoryInterface $menuCategoryRepo,
        public readonly MenuRepositoryInterface $menuRepo,
        public readonly UserRepositoryInterface $userRepo,
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly TableRepositoryInterface $tableRepo,
        public readonly ItemizedOrderRepositoryInterface $itemizedOrderRepo,
        public readonly OrderRepositoryInterface $orderRepo,
        public readonly StoreDetailRepositoryInterface $storeDetailRepo,
        public readonly PaymentMethodRepositoryInterface $paymentMethodRepo,
        public readonly NumberOfCustomerRepositoryInterface $numberOfCustomerRepo,
        public readonly StoreRoleRepositoryInterface $storeRoleRepo,

        public readonly BillServiceInterface $billServ,
        public readonly OrderServiceInterface $orderServ,
    ) {
    }

    public function store(Request $request)
    {
        // 伝票情報の取得
        $bill = $this->billRepo->find($request->itemized_order['bill_id']);
        if (is_null($bill)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['伝票情報の読み込みができませんでした']
            ], 404);
        }

        // ストアの取得
        $store = $this->storeRepo->findStore($bill->store_id);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            // ItemizedOrder作成
            $itemizedOrder = $this->itemizedOrderRepo->createItemizedOrder($request->itemized_order['bill_id']);

            // Order作成
            foreach ($request->itemized_order['selected_orders'] as $selectedOrder) {
                $this->orderServ->createOrders(
                    $this->storeDetailRepo->getLatestStoreDetail($store),
                    $itemizedOrder->id,
                    $selectedOrder['menu_id'],
                    $selectedOrder['quantity'],
                    $selectedOrder['incentive_target']['type'],
                    $selectedOrder['incentive_target']['id']
                );
            }

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

        $createdItemizedOrder = $this->itemizedOrderRepo->find($itemizedOrder->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'bill' => $bill,
                'itemizedOrder' => $createdItemizedOrder
            ]
        ], 200);
    }

    public function update(UpdateOrderRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->store_id);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            $this->orderServ->updateOrder(
                $this->storeDetailRepo->getLatestStoreDetail($store),
                $request->itemized_order_id,
                $request->order_ids,
                $request->quantity,
                $request->order['adjust_amount'],
                $request->user_incentive['amount'] ?? null,
                $request->sys_menu_category_id
            );

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

        $updatedItemizedOrder = $this->itemizedOrderRepo->find($request->itemized_order_id);

        return response()->json([
            'status' => 'success',
            'data' => $updatedItemizedOrder
        ], 200);
    }

    public function archive(Request $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->store_id);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            foreach ($request->orderIds as $orderId) {
                $this->orderRepo->softDelete($orderId);
            }

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







    public function index(Store $store, Bill $bill)
    {
        // テーブル一覧取得(伝票情報含む)
        $tables = $this->billServ->getAllDisplayTablesWithStatus($store);

        $sysMenuCategories = SysMenuCategory::CATEGORIES;

        $storeDetail = $this->storeDetailRepo->getLatestStoreDetail($store);

        $DIGITS = Digit::getAllDigits();
        $ROUNDING_METHODS = RoundingMethod::getAllMethods();

        $SYS_PAYMENT_METHOD_CATEGORIES = SysPaymentMethodCategory::SYS_PAYMENT_METHOD_CATEGORIES;

        $storePaymentMethods = $this->paymentMethodRepo->getStorePaymentMethods($store);

        $issuedStatus = Bill::ISSUED_STATUS;

        // 延長セット料金を取得
        $extensionSetMenus = $this->menuRepo->getDisplayMenuListByStoreAndSysMenuCategoryIds($store, SysMenuCategory::CATEGORIES['EXTENSION_SET']['id']);

        // 人数を取得
        $numberOfCustomer = $this->numberOfCustomerRepo->getBillNumberOfCustomer($bill);

        return view('order.index', compact(
            'store',
            'storeDetail',
            'bill',
            'tables',
            'sysMenuCategories',
            'DIGITS',
            'ROUNDING_METHODS',
            'SYS_PAYMENT_METHOD_CATEGORIES',
            'storePaymentMethods',
            'issuedStatus',
            'extensionSetMenus',
            'numberOfCustomer'
        ));
    }

    public function create(Store $store, Bill $bill)
    {
        // カテゴリ一覧を取得
        $drinkFoodCategories = $this->menuCategoryRepo->getMenuCategoryListByStoreAndSysMenuCategoryIds(
            $store,
            SysMenuCategory::CATEGORIES['DRINK_FOOD']['id'],
            ['*'],
            'name'
        );

        $selectionCategories = $this->menuCategoryRepo->getMenuCategoryListByStoreAndSysMenuCategoryIds(
            $store,
            SysMenuCategory::CATEGORIES['SELECTION']['id'],
            ['*'],
            'name'
        );

        // カテゴリごとのメニュー一覧を取得
        foreach ($drinkFoodCategories as $key => $drinkFoodCategory) {
            $drinkFoodCategories[$key]['menus'] = $this->menuRepo->getDisplayMenusByMenuCategory(
                $drinkFoodCategory
            );
        }

        foreach ($selectionCategories as $key => $selectionCategory) {
            $selectionCategories[$key]['menus'] = $this->menuRepo->getDisplayMenusByMenuCategory(
                $selectionCategory
            );
        }
        $menus = [
            $selectionCategories,
            $drinkFoodCategories
        ];

        /**
         * 出勤ユーザー一覧を取得する
         */
        // 店舗に属するストアロール一覧を取得
        $storeRoles = $this->storeRoleRepo->getStoreRoles($store->id);

        // 営業日付を取得
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);

        foreach ($storeRoles as $key => $storeRole) {
            $storeRoles[$key]['users'] = $this->userRepo->getAttendanceUsersByStoreRole($storeRole, $businessDate);
        }

        // テーブル一覧取得(伝票情報含む)
        $tables = $this->billServ->getAllDisplayTablesWithStatus($store);

        $incentiveTargets = UserIncentive::INCENTIVE_TARGET;

        $sysMenuCategories = SysMenuCategory::CATEGORIES;

        return view('order.create', compact('store', 'bill', 'tables', 'menus', 'storeRoles', 'incentiveTargets', 'sysMenuCategories'));
    }
}
