<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\{
    BillRepository\BillRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};

class StoreReportController extends Controller
{
    public function __construct(
        public readonly BillRepositoryInterface $billRepo,
        public readonly StoreRepositoryInterface $storeRepo,
    ) {}

    public function get(int $storeId, string $yearMonth)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        $bills = $this->billRepo->getYearMonthStoreBills($storeId, $yearMonth);

        return response()->json([
            'status' => 'success',
            'data' => $bills
        ], 200);
    }
}
