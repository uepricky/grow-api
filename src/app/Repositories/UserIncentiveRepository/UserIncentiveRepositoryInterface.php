<?php

namespace App\Repositories\UserIncentiveRepository;

use Illuminate\Support\{
    Collection
};
use App\Models\{
    UserIncentive,
    Order,
    Store
};

interface UserIncentiveRepositoryInterface
{
    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * ユーザーインセンティブレコードの作成
     * @param array $data
     * @return UserIncentive
     */
    public function createUserIncentive(array $data): UserIncentive;

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * 注文に紐づくユーザーインセンティブ取得
     * @param Order $order
     * @return UserIncentive
     */
    public function getOrderUserIncentive(Order $order): UserIncentive;

    /**
     * 注文ID(複数)に紐づくユーザーインセンティブ取得
     * @param array $orderIds
     * @return Collection
     */
    public function getOrdersUserIncentives(array $orderIds): Collection;

    /**
     * 指定期間に紐づくユーザーインセンティブ取得
     * @param Store $store
     * @param string $yearMonth
     * @return Collection
     */
    public function getSelectedDateUserInsentives(Store $store, string $yearMonth): Collection;


    /***********************************************************
     * Update系
     ***********************************************************/

    /***********************************************************
     * Delete系
     ***********************************************************/
    /**
     * ソフトデリートする
     * @param UserIncentive $userIncentive
     * @return bool
     */
    public function softDeletes(UserIncentive $userIncentive): bool;

    /***********************************************************
     * その他
     ***********************************************************/
}
