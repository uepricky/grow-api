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
    DeductionRepository\DeductionRepositoryInterface
};

class DeductionController extends Controller
{
    public function __construct(
        public readonly UserRepositoryInterface $userRepo,
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly AttendanceRepositoryInterface $attendanceRepo,
        public readonly DeductionRepositoryInterface $deductionRepo,
    ) {
    }

    public function get($storeId, $businessDate, $userId)
    {
        // BusinessDateを取得
        $businessDate = $this->businessDateRepo->getBusinessDateByStoreIdAndDate(
            $storeId,
            $businessDate
        );

        if (is_null($businessDate)) {
            // 開店画面へ遷移
        }

        // Userを取得
        $targetUser = $this->userRepo->find($userId);

        // Attendanceを取得
        $attendance = $this->attendanceRepo->getStoreUserAttendance(
            $targetUser,
            $businessDate
        );

        if (is_null($attendance)) {
            return response([
                'status' => 'success',
                'message' => '取得しました。',
                'data' => []
            ], 200);
        }

        // 控除一覧を取得
        $deductions = $this->deductionRepo->getAttendanceDeductions($attendance);


        return response([
            'status' => 'success',
            'message' => '取得しました。',
            'data' => $deductions
        ], 200);
    }

    public function updateOrInsert(DeductionRequest $request, $storeId, $businessDate)
    {
        // 更新または保存
        $store = $this->storeRepo->findStore($request->attendanceIdentifier['store_id']);
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);
        $targetUser = $this->userRepo->find($request->attendanceIdentifier['user_id']);
        $attendance = $this->attendanceRepo->getStoreUserAttendance($targetUser, $businessDate);

        // トランザクションを開始する
        DB::beginTransaction();
        try {
            if (is_null($attendance)) {
                $attendance = $this->attendanceRepo->createAttendance(
                    [
                        'user_id' => $targetUser->id,
                        'business_date_id' => $businessDate->id
                    ]
                );
            }

            $deductionData = [];
            foreach ($request->deductions as $deduction) {
                if (
                    isset($deduction['name']) &&
                    isset($deduction['amount'])
                ) {
                    $deductionData[] = [
                        'attendance_id' => $attendance->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                        ...$deduction
                    ];
                }
            }

            // 削除
            $this->deductionRepo->deleteDeductions($attendance->id);

            // 登録
            $this->deductionRepo->insertDeductions($deductionData);

            DB::commit();
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバックする
            DB::rollback();

            // ログの出力
            CustomLog::error($e);

            return response()->json([
                'status' => 'failure',
                'errors' => [$e->getMessage()]
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => '保存しました。',
            'data' => $deductionData
        ], 200);
    }
}
