<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdRequest;
use Illuminate\Http\Request;
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface,
    BusinessDateRepository\BusinessDateRepositoryInterface,
    UserRepository\UserRepositoryInterface,
    StoreRoleRepository\StoreRoleRepositoryInterface,
};

class RollController extends Controller
{
    public function __construct(
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly UserRepositoryInterface $userRepo,
        public readonly StoreRoleRepositoryInterface $storeRoleRepo,
    ) {
    }

    public function getStoreRoles(StoreIdRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->storeId);

        // 営業日付を取得
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);

        $storeRoles = $this->storeRoleRepo->getStoreRoles($store->id);

        foreach ($storeRoles as $key => $storeRole) {
            $storeRoles[$key]['users'] = $this->userRepo->getAttendanceUsersByStoreRole($storeRole, $businessDate);
        }

        return response()->json([
            'status' => 'success',
            'data' => $storeRoles
        ], 200);
    }
}
