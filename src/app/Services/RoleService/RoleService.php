<?php

namespace App\Services\RoleService;

use Illuminate\Support\Collection;
use App\Services\RoleService\RoleServiceInterface;
use App\Models\{
    Group,
};
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface,
    StoreRoleRepository\StoreRoleRepositoryInterface
};


class RoleService implements RoleServiceInterface
{
    public function __construct(
        public readonly StoreRepositoryInterface $storeRepo,

        public readonly StoreRoleRepositoryInterface $storeRoleRepo,
    ) {
    }

    /**
     * グループに属するストア一覧のストアロール一覧を取得
     * @param Group $group
     * @return Collection
     */
    public function getStoresRolesByGroup(Group $group): Collection
    {
        // 店舗一覧の取得
        $stores = $this->storeRepo->getStoreListByGroup(
            $group,
            ['id', 'name']
        );

        // 店舗ごとにデフォルトストアロールを取得し、新しいフィールドとして追加する
        $stores->transform(function ($store) {
            $store['storeRoles'] = $this->storeRoleRepo->getStoreRoles($store->id);
            return $store;
        });

        return $stores;
    }
}
