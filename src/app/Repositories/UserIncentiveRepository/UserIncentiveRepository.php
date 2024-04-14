<?php

namespace App\Repositories\UserIncentiveRepository;

use Illuminate\Support\{
    Carbon,
    Collection
};
use App\Models\{
    UserIncentive,
    Order,
    Store
};

class UserIncentiveRepository implements UserIncentiveRepositoryInterface
{
    private $model;

    public function __construct(UserIncentive $userIncentive)
    {
        $this->model = $userIncentive;
    }

    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * ユーザーインセンティブレコードの作成
     * @param array $data
     * @return UserIncentive
     */
    public function createUserIncentive(array $data): UserIncentive
    {
        return $this->model->create($data);
    }

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * 注文に紐づくユーザーインセンティブ取得
     * @param Order $order
     * @return UserIncentive
     */
    public function getOrderUserIncentive(Order $order): UserIncentive
    {
        return $order->userIncentive;
    }

    /**
     * 注文ID(複数)に紐づくユーザーインセンティブ取得
     * @param array $orderIds
     * @return Collection
     */
    public function getOrdersUserIncentives(array $orderIds): Collection
    {
        return $this->model->whereIn('order_id', $orderIds)->get();
    }

    /**
     * 指定期間に紐づくユーザーインセンティブ取得
     * @param Store $store
     * @param string $yearMonth
     * @return Collection
     */
    public function getSelectedDateUserInsentives(Store $store, string $yearMonth): Collection
    {
        $startOfMonth = date("Y-m-01", strtotime($yearMonth));
        $endOfMonth = date("Y-m-t", strtotime($yearMonth));

        return $this->model->whereHas('order', function ($orderQuery) use ($store, $startOfMonth, $endOfMonth) {
            $orderQuery->whereHas('itemizedOrder', function ($itemizedOrderQuery) use ($store, $startOfMonth, $endOfMonth) {
                $itemizedOrderQuery->whereHas('bill', function ($billQuery) use ($store, $startOfMonth, $endOfMonth) {
                    $billQuery->where('store_id', $store->id);

                    $billQuery->whereHas('businessDate', function ($businessDateQuery) use ($startOfMonth, $endOfMonth) {
                        $businessDateQuery->whereBetween('business_date', [
                            $startOfMonth,
                            $endOfMonth
                        ]);
                        $businessDateQuery->whereNotNull('closing_time');
                    });
                });
            });
        })
        ->with([
            'order.itemizedOrder.bill.businessDate',
            'order.menu.menuCategory'
        ])
        ->get();
    }

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
    public function softDeletes(UserIncentive $userIncentive): bool
    {
        return $userIncentive->delete();
    }

    /***********************************************************
     * その他
     ***********************************************************/
}
