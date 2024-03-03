<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface,
};
use App\Services\{
    RoleService\RoleServiceInterface
};

class GroupController extends Controller
{
    public function __construct(
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly RoleServiceInterface $roleServ,
    ) {
    }

    public function getStores()
    {
        // グループの取得
        $group = auth()->user()->groups->first();

        // グループに属する店舗一覧を取得
        $stores = $this->storeRepo->getStoreListByGroup($group);

        return response([
            'status' => 'success',
            'data' => $stores
        ], 200);
    }

    public function getStoresWithRoles()
    {
        // グループの取得
        $group = auth()->user()->groups->first();

        // グループに属するストア一覧のストアロール一覧を取得
        $storesWithRoles = $this->roleServ->getStoresRolesByGroup($group);

        return response([
            'status' => 'success',
            'data' => [
                'storesWithRoles' => $storesWithRoles
            ]
        ], 200);
    }
}
