<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface,
    UserIncentiveRepository\UserIncentiveRepositoryInterface
};

class UserIncentiveController extends Controller
{
    public function __construct(
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly UserIncentiveRepositoryInterface $userIncentiveRepo,
    ) {
    }

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

        $userIncentives = $this->userIncentiveRepo->getSelectedDateUserInsentives($store, $yearMonth);

        return response()->json([
            'status' => 'success',
            'data' => $userIncentives
        ], 200);
    }
}
