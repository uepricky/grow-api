<?php

namespace App\Repositories\DeductionRepository;

use Illuminate\Support\Collection;
use App\Repositories\DeductionRepository\DeductionRepositoryInterface;
use App\Models\{
    Deduction,
    Store,
    Attendance,
};

class DeductionRepository implements DeductionRepositoryInterface
{
    public function __construct(Deduction $store)
    {
        $this->model = $store;
    }

    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * 控除を登録
     * @param array $data
     */
    public function insertDeductions(array $data)
    {
        return $this->model->insert($data);
    }


    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * Attendanceに紐づく控除一覧を取得する
     * @param Attendance $attendance
     * @return Collection
     */
    public function getAttendanceDeductions(Attendance $attendance): Collection
    {
        return $attendance->deductions;
    }


    /***********************************************************
     * Update系
     ***********************************************************/


    /***********************************************************
     * Delete系
     ***********************************************************/
    /**
     * 勤怠IDに紐づく控除を削除する
     * @param int $attendanceId
     */
    public function deleteDeductions(int $attendanceId)
    {
        return $this->model->where('attendance_id', $attendanceId)->delete();
    }

    /***********************************************************
     * その他
     ***********************************************************/
}
