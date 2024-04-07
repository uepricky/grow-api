<?php

namespace App\Services\StoreService;
use App\Models\{
    Store,
    PermissionV2Permission
};

interface StoreServiceInterface
{
    const DEFAULT_STORE_ROLES = [
        'MANAGER' => [
            'name' => 'マネージャー',
            'permissionIds' => [
                PermissionV2Permission::PERMISSIONS['OPERATION_UNDER_STORE_DASHBOARD']['id']
            ]
        ],
        'STAFF' => [
            'name' => 'スタッフ',
            'permissionIds' => []
        ],
        'CAST' => [
            'name' => 'キャスト',
            'permissionIds' => []
        ],
    ];

    /**
     * 店舗を作成する
     * @param array $store
     * @param array $storeDetail
     * @return Store
     */
    public function createStore(array $store, array $storeDetail): Store;
}
