<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\{
    DeductionRequest
};
use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;
use App\Repositories\{
    UserRepository\UserRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
    BusinessDateRepository\BusinessDateRepositoryInterface,
    AttendanceRepository\AttendanceRepositoryInterface,
};

class DeductionController extends Controller
{
    public function __construct(
        public readonly UserRepositoryInterface $userRepo,
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly AttendanceRepositoryInterface $attendanceRepo,
    ) {
    }

    public function updateOrInsert(DeductionRequest $request)
    {
        // 更新または保存
        $store = $this->storeRepo->findStore($request->attendanceIdentifier['store_id']);
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);
        $targetUser = $this->userRepo->find($request->attendanceIdentifier['user_id']);
        $attendance = $this->attendanceRepo->getStoreUserAttendance($targetUser, $businessDate);

        $deductionData = [
            'attendance_id' => $attendance->id,
            ...$request->deduction
        ];

        // 上記$deductionDataをupdateOrCreateここから

        return response([
            'status' => 'success',
            'message' => '保存しました。',
            'data' => $deductionData
        ], 200);
    }
}
