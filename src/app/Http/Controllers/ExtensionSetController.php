<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExtensionSet\ExtensionSetRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use App\Log\CustomLog;
use App\Models\{
    Store,
    Bill,
    ItemizedOrder
};
use App\Repositories\{
    BillRepository\BillRepositoryInterface,
    StoreDetailRepository\StoreDetailRepositoryInterface,
    ItemizedOrderRepository\ItemizedOrderRepositoryInterface,
    OrderRepository\OrderRepositoryInterface,
    BusinessDateRepository\BusinessDateRepositoryInterface,
    NumberOfCustomerRepository\NumberOfCustomerRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};
use App\Services\{
    OrderService\OrderServiceInterface,
};

class ExtensionSetController extends Controller
{
    public function __construct(
        public readonly BillRepositoryInterface $billRepo,
        public readonly StoreDetailRepositoryInterface $storeDetailRepo,
        public readonly ItemizedOrderRepositoryInterface $itemizedOrderRepo,
        public readonly OrderRepositoryInterface $orderRepo,
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly NumberOfCustomerRepositoryInterface $numberOfCustomerRepo,
        public readonly StoreRepositoryInterface $storeRepo,

        public readonly OrderServiceInterface $orderServ,
    ) {}

    public function store(ExtensionSetRequest $request)
    {
        $bill = $this->billRepo->find($request->bill_id);
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

         // Policy確認
        try {
            $this->authorize('create', [ItemizedOrder::class, $store, $bill, $request->bill_id]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['この操作を実行する権限がありません']
            ], 403);
        }

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            // itemized_orders
            $itemizedOrder = $this->itemizedOrderRepo->createItemizedOrder($request->bill_id);

            // 延長セット注文の作成
            $this->orderServ->createExtensionSetOrders(
                $itemizedOrder->id,
                $request->orders['extension_set_id'],
                $request->orders['quantity']
            );

            DB::commit();
        }catch (\Throwable $e) {
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
            'data' => $createdItemizedOrder
        ], 200);
    }
}
