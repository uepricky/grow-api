<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreIdRequest;
use App\Models\{
    Store,
};
use App\Repositories\{
    BusinessDateRepository\BusinessDateRepositoryInterface,
    CashRegisterRepository\CashRegisterRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,

};
use App\Services\StoreSalesService\StoreSalesServiceInterface;
use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;

class ClosingStoreController extends Controller
{
    public function __construct(
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly CashRegisterRepositoryInterface $cashRegisterRepo,
        public readonly StoreRepositoryInterface $storeRepo,

        public readonly StoreSalesServiceInterface $storeSalesServ,
    ) {
    }

    public function preparation(StoreIdRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // 営業日の取得
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);
        if (is_null($businessDate)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['営業日情報の読み込みができませんでした']
            ], 404);
        }

        $businessDayReport = $this->storeSalesServ->getBusinessDayReport($store, $businessDate);
        if (is_null($businessDayReport)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['営業情報の読み込みができませんでした']
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $businessDayReport
        ], 200);
    }

    public function register(Request $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->store_id);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        DB::beginTransaction();

        try {
            // 営業日を現在日時で終了する
            $this->businessDateRepo->updateBusinessDate($request->business_date_id, [
                'closing_time' => now()
            ]);

            // レジ金登録情報があれば更新する
            if (isset($request->cash_register)) {
                $this->cashRegisterRepo->updateCashRegisterByBusinessDateId($request->business_date_id, $request->cash_register);
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
}
