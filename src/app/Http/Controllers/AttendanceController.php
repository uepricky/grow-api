<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\{
    AttendanceRequest,
    TardyAbsenceRequest,
    PayrollPaymentRequest
};
use App\Http\Requests\StoreIdRequest;
use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;
use App\Models\{
    Attendance,
    Permission
};
use Illuminate\Http\Request;
use App\Repositories\{
    UserRepository\UserRepositoryInterface,
    BusinessDateRepository\BusinessDateRepositoryInterface,
    AttendanceRepository\AttendanceRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};
use App\Services\{
    AttendanceService\AttendanceServiceInterface,
};
use App\Services\UserService\UserServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;


class AttendanceController extends Controller
{
    public function __construct(
        public readonly UserRepositoryInterface $userRepo,
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly AttendanceRepositoryInterface $attendanceRepo,
        public readonly StoreRepositoryInterface $storeRepo,

        public readonly AttendanceServiceInterface $attendanceServ,
        public readonly UserServiceInterface $userServ,
    ) {
    }

    public function get(int $storeId, string $businessDate)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // 営業日付を取得
        // TODO: 引数の$businessDateを元にモデル取得へ修正
        $businessDate = $this->businessDateRepo->getCurrentBusinessDate($store);
        if (!$businessDate) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['営業情報の読み込みができませんでした']
            ], 404);
        }

        // ストアに紐づくユーザー一覧を取得
        $users = $this->userRepo->getStoreUsers($store);

        // ユーザーに紐づく勤怠情報を取得
        $usersWithAttendance = $this->attendanceServ->getFormattedAttendanceInfo($users, $businessDate, $store);

        return response()->json([
            'status' => 'success',
            'data' => [
                'usersWithAttendance' => $usersWithAttendance,
                'store' => $store
            ]
        ], 200);
    }

    public function bulkUpdate(AttendanceRequest $request, int $storeId, string $businessDate)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            $this->attendanceServ->updateOrInsertAttendances($request, $store);

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

        return response()->json([
            'status' => 'success',
            'data' => []
        ], 200);
    }

    public function updateTardyAbsence(TardyAbsenceRequest $request, int $storeId, string $businessDate)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            $this->attendanceServ->updateOrInsertTardyAbsenceInfo($request, $store);

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

        return response()->json([
            'status' => 'success',
            'data' => []
        ], 200);
    }

    public function updatePayrollPayment(PayrollPaymentRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->store_id);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // 権限チェック
        $hasPermission = $this->userServ->hasStorePermission(
            $request->user(),
            $store,
            Permission::PERMISSIONS['OPERATION_UNDER_STORE_DASHBOARD']['id']
        );
        if (!$hasPermission) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['この操作を実行する権限がありません']
            ], 403);
        }

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            $this->attendanceServ->updateOrInsertPayrollPaymentInfo($request, $store);

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

        return response()->json([
            'status' => 'success',
            'data' => []
        ], 200);
    }
}
