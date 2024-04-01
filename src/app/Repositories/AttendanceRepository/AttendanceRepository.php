<?php

namespace App\Repositories\AttendanceRepository;

use App\Models\{
    Attendance,
    BusinessDate,
    User,
    Store
};
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    private $model;

    public function __construct(Attendance $attendance)
    {
        $this->model = $attendance;
    }

    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * attendanceレコードを作成する
     * @param array $data
     * @return Attendance
     */
    public function createAttendance(array $data): Attendance
    {
        return $this->model->create($data);
    }

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * ユーザーに紐づく指定の店舗の勤怠情報を取得
     * @param User $user
     * @param BusinessDate $businessDate
     * @return ?Attendance
     */
    public function getStoreUserAttendance(User $user, BusinessDate $businessDate): ?Attendance
    {
        return $user->attendances()
            ->where('business_date_id', $businessDate->id)
            ->first();
    }

    /**
     * 営業日に紐づく出退勤一覧取得
     * @param BusinessDate $businessDate
     *
     * @return Collection
     */
    public function getBusinessDateAttendances(BusinessDate $businessDate): Collection
    {
        return $this->model->where('business_date_id', $businessDate->id)->get();
    }

    /**
     * @param Store $store
     * @param string $yearMonth
     * @param int $storeRoleId
     *
     * @return Collection
     */
    public function getSpecifiedPeriodAttendances(Store $store, string $yearMonth, int $storeRoleId): Collection
    {
        $startOfMonth = date("Y-m-01", strtotime($yearMonth));
        $endOfMonth = date("Y-m-t", strtotime($yearMonth));

        return $this->model->whereHas('businessDate', function ($query) use ($store, $startOfMonth, $endOfMonth) {
            $query->where('store_id', $store->id);
            $query->whereBetween('business_date', [
                $startOfMonth,
                $endOfMonth
            ]);
        })
        ->whereHas('user', function ($query) use ($storeRoleId) {
            $query->whereHas('storeRoles', function ($storeRoleQuery) use ($storeRoleId) {
                $storeRoleQuery->where('store_role_id', $storeRoleId);
            });
        })
        ->whereNotNull('working_end_at')
        ->get();
    }

    /***********************************************************
     * Update系
     ***********************************************************/
    /**
     * 勤退情報を作成または、更新する
     * @param int $user_id
     * @param int $business_date_id
     * @param array $data
     */
    public function updateOrInsertAttendance(int $user_id, int $business_date_id, array $data)
    {
        $this->model->updateOrInsert(
            [
                'user_id' => $user_id,
                'business_date_id' => $business_date_id
            ],
            [
                ...$data,
                'updated_at' => now(),
            ]
        );
    }

    /***********************************************************
     * Delete系
     ***********************************************************/

    /***********************************************************
     * その他
     ***********************************************************/
}
