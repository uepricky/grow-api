<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Store,
    Bill,
    BillPayment
};
use App\Repositories\{
    BillRepository\BillRepositoryInterface,
    BillPaymentRepository\BillPaymentRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};
use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;

class BillPaymentController extends Controller
{
    public function __construct(
        public readonly BillRepositoryInterface $billRepo,
        public readonly BillPaymentRepositoryInterface $billPaymentRepo,
        public readonly StoreRepositoryInterface $storeRepo,

    ) {
    }

    public function store(Request $request)
    {
        $bill = $this->billRepo->find($request->bill['bill_id']);
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

        DB::beginTransaction();

        try {
            $insertData = [];
            foreach ($request->bill_payments as $i => $billPayment) {
                $insertData[$i] = $billPayment;
                $insertData[$i]['created_at'] = now();
                $insertData[$i]['updated_at'] = now();
            }

            // 支払い登録
            $this->billPaymentRepo->insertBillPayment($insertData);

            // 伝票の支払いフラグ登録
            $bill->receipt_issued = $request->bill['receipt_issued'];
            $bill->invoice_issued = $request->bill['invoice_issued'];
            $bill->memo = $request->bill['memo'];
            $bill->total_amount = $request->bill['total_amount'];
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

        $createdBillPayment = $this->billPaymentRepo->getLatestBillPayment($bill);

        return response()->json([
            'status' => 'success',
            'data' => $createdBillPayment
        ], 200);
    }

    public function cancel(int $billId)
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

        // 支払い伝票論理削除
        $billPayments = $this->billPaymentRepo->getBillPayments($bill);

        DB::beginTransaction();

        try {
            foreach ($billPayments as $billPayment) {
                $this->billPaymentRepo->softDeleteBillPayment($billPayment);
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
