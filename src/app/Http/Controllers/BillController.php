<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Bill\BillRequest;
use App\Http\Requests\StoreIdRequest;
use App\Models\{
    Store,
    Bill,
};
use App\Repositories\{
    BillRepository\BillRepositoryInterface,
    StoreDetailRepository\StoreDetailRepositoryInterface,
    ItemizedOrderRepository\ItemizedOrderRepositoryInterface,
    OrderRepository\OrderRepositoryInterface,
    BusinessDateRepository\BusinessDateRepositoryInterface,
    NumberOfCustomerRepository\NumberOfCustomerRepositoryInterface,
    MenuRepository\MenuRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};
use App\Services\{
    OrderService\OrderServiceInterface,
};
use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;
use App\Utils\TimeUtility;

class BillController extends Controller
{
    public function __construct(
        public readonly BillRepositoryInterface $billRepo,
        public readonly StoreDetailRepositoryInterface $storeDetailRepo,
        public readonly ItemizedOrderRepositoryInterface $itemizedOrderRepo,
        public readonly OrderRepositoryInterface $orderRepo,
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly NumberOfCustomerRepositoryInterface $numberOfCustomerRepo,
        public readonly MenuRepositoryInterface $menuRepo,
        public readonly StoreRepositoryInterface $storeRepo,

        public readonly OrderServiceInterface $orderServ,
    ) {}

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

        // Policy確認
        // try {
        //     $this->authorize('viewAny', [Attendance::class, $store]);
        // } catch (AuthorizationException $e) {
        //     return response()->json([
        //         'status' => 'failure',
        //         'errors' => ['この操作を実行する権限がありません']
        //     ], 403);
        // }

        // 開始日時を取得
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);

        // 伝票情報を取得
        $bills = $this->billRepo->getBusinessDateBills($store, $businessDate);

        return response()->json([
            'status' => 'success',
            'data' => [
                'bills' => $bills,
                'businessDate' => $businessDate
            ]
        ], 200);
    }

    public function get(int $id)
    {
        $bill = $this->billRepo->find($id);

        return response()->json([
            'status' => 'success',
            'data' => $bill
        ], 200);
    }

    public function store(BillRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->bill['store_id']);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // Policy確認
        // try {
        //     $this->authorize('viewAny', [Attendance::class, $store]);
        // } catch (AuthorizationException $e) {
        //     return response()->json([
        //         'status' => 'failure',
        //         'errors' => ['この操作を実行する権限がありません']
        //     ], 403);
        // }

        // 開始日時を取得
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);
        list($hh, $mm) = explode(':', $request->start_at);
        $startAt = TimeUtility::caluculateDateTime($businessDate, $hh, $mm);

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            // Bill
            $storeDetail = $this->storeDetailRepo->getLatestStoreDetail($store);

            $billData = [
                'business_date_id' => $request->bill['business_date_id'],
                'store_id' => $request->bill['store_id'],
                'arrival_time' => $startAt,
                'store_detail_id' => $storeDetail->id,
                'service_rate' => $storeDetail->service_rate,
                'consumption_tax_rate' => $storeDetail->consumption_tax_rate
            ];

            $bill = $this->billRepo->createBill($billData);

            // tableとのアタッチ
            $this->billRepo->attachTablesToBill($bill, $request->tables);

            // itemized_orders
            $itemizedOrder = $this->itemizedOrderRepo->createItemizedOrder($bill->id);

            // oredrs + SetOrder
            // TODO: リクエストバリデーションで、複数オーダーの制約忘れずに
            foreach ($request->orders as $requestOrder) {
                if (empty($requestOrder['quantity'])) {
                    continue;
                }

                // 初回セット注文の作成
                $this->orderServ->createFirstSetOrders(
                    $itemizedOrder->id,
                    $requestOrder['first_set_id'],
                    $requestOrder['quantity'],
                    $startAt
                );

                // NumberOfCustomer
                $this->numberOfCustomerRepo->createNumberOfCustomer($bill->id, $requestOrder['quantity']);
            }

            $createdBill = $this->billRepo->find($bill->id);

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
            'data' => [
                'bill' => $createdBill
            ]
        ], 200);
    }

    public function departure(int $billId)
    {
        $bill = $this->billRepo->find($billId);
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
            $bill->departure_time = now();

            //
            $this->billRepo->updateBill($bill);

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

    public function archive(Store $store, Bill $bill)
    {
        // トランザクションを開始する
        DB::beginTransaction();
        try {
            //
            $this->billRepo->softDeleteBill($bill);

            DB::commit();
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバックする
            DB::rollback();

            // ログの出力
            CustomLog::error($e);
            abort(500);
        }


        return redirect()->route('halls.index', compact('store'))->with('message', '伝票を削除しました。');
    }
}
