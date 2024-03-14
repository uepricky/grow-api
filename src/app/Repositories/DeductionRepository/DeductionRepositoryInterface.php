<?php

namespace App\Repositories\DeductionRepository;

use Illuminate\Support\Collection;
use App\Models\{
    Deduction,
    Store,
    Attendance,
};

interface DeductionRepositoryInterface
{
    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * 控除を登録
     * @param array $data
     */
    public function insertDeductions(array $data);

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * Attendanceに紐づく控除一覧を取得する
     * @param Attendance $attendance
     * @return Collection
     */
    public function getAttendanceDeductions(Attendance $attendance): Collection;

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
    public function deleteDeductions(int $attendanceId);

    /***********************************************************
     * その他
     ***********************************************************/
}
