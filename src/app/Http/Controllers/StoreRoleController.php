<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface,
    StoreRoleRepository\StoreRoleRepositoryInterface
};

class StoreRoleController extends Controller
{
    public function __construct(
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly StoreRoleRepositoryInterface $storeRoleRepo,
    ) {}

    public function getAll(int $storeId)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        $storeRoles = $this->storeRoleRepo->getStoreRoles($storeId);

        return response()->json([
            'status' => 'success',
            'data' => $storeRoles
        ], 200);
    }
}
