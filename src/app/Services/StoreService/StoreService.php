<?php

namespace App\Services\StoreService;

use App\Services\StoreService\StoreServiceInterface;
use App\Models\{
    PermissionV2Permission,
    Store,
    RouteActionTarget,
    Table,
};
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface,
    StoreDetailRepository\StoreDetailRepositoryInterface,
    TableRepository\TableRepositoryInterface,
};
use App\Repositories\StoreRoleRepository\StoreRoleRepositoryInterface;

class StoreService implements StoreServiceInterface
{

    const DUMMY = [
        'TABLES' => [
            ['name' => '1卓', 'display' => Table::DISPLAY['TRUE']],
            ['name' => '2卓', 'display' => Table::DISPLAY['TRUE']],
            ['name' => '6卓', 'display' => Table::DISPLAY['TRUE']],
            ['name' => '3卓', 'display' => Table::DISPLAY['TRUE']],
            ['name' => '4卓', 'display' => Table::DISPLAY['TRUE']],
            ['name' => '5卓', 'display' => Table::DISPLAY['TRUE']],
        ]
    ];

    // const DEFAULT_STORE_ROLES = [
    //     'MANAGER' => [
    //         'name' => 'マネージャー',
    //         'permissionIds' => [
    //             PermissionV2Permission::PERMISSIONS['OPERATION_UNDER_STORE_DASHBOARD']['id']
    //         ]
    //     ],
    //     'STAFF' => [
    //         'name' => 'スタッフ',
    //         'permissionIds' => []
    //     ],
    //     'CAST' => [
    //         'name' => 'キャスト',
    //         'permissionIds' => []
    //     ],
    // ];

    public function __construct(
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly StoreDetailRepositoryInterface $storeDetailRepo,
        public readonly TableRepositoryInterface $tableRepo,

        public readonly StoreRoleRepositoryInterface $storeRoleRepo,
    ) {
    }

    /**
     * 店舗を作成する
     * @param array $store
     * @param array $storeDetail
     * @return Store
     */
    public function createStore(array $store, array $storeDetail): Store
    {
        // 店舗作成
        $createdStore = $this->storeRepo->createStore($store);

        // 店舗詳細作成
        $storeDetail = $this->storeDetailRepo->createStoreDetail($createdStore, $storeDetail);

        // ストアロールの作成
        $storeRolesData = self::DEFAULT_STORE_ROLES;
        foreach ($storeRolesData as $storeRoleData) {
            $storeRoleData['store_id'] = $createdStore->id;
            $storeRole = $this->storeRoleRepo->createStoreRole($storeRoleData);

            // ストアロールに権限を付与
            $this->storeRoleRepo->attachPermissionsToStoreRole($storeRole, $storeRoleData['permissionIds']);
        }

        // デフォルトストラロール一覧をストアに付与
        // $defaultRoles = $this->roleRepo->getAllDefaultStoreRoles();
        // $defaultRoleIds = $defaultRoles->pluck("id")->toArray();
        // $this->roleRepo->attachRolesToStore($defaultRoleIds, $createdStore);

        // ダミー卓を設定
        foreach ($this::DUMMY['TABLES'] as $table) {
            $table['store_id'] = $createdStore->id;
            $this->tableRepo->createTable($table);
        }

        return $createdStore;
    }
}
