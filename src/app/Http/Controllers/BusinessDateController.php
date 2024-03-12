<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdRequest;
use App\Repositories\{
    BusinessDateRepository\BusinessDateRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};
class BusinessDateController extends Controller
{
    public function __construct(
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly StoreRepositoryInterface $storeRepo,
    ) {}

    public function getCurrentBusinessDate(int $storeId)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // 営業日付を取得
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);
        // if (!$businessDate) {
        //     return response()->json([
        //         'status' => 'failure',
        //         'errors' => ['営業情報の読み込みができませんでした']
        //     ], 404);
        // }

        return response()->json([
            'status' => 'success',
            'data' => $businessDate
        ], 200);
    }
}
