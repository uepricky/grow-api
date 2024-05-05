<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\BusinessDate;
use Illuminate\Http\Request;
use App\Http\Requests\StoreIdRequest;
use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;
use App\Models\{
    Store,
    Permission
};
use App\Repositories\{
    UserRepository\UserRepositoryInterface,
    BusinessDateRepository\BusinessDateRepositoryInterface,
    CashRegisterRepository\CashRegisterRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};
use App\Services\UserService\UserServiceInterface;
use App\Http\Requests\OpeningPreparation\OpeningPreparationRequest;
use Illuminate\Auth\Access\AuthorizationException;

class OpeningPreparationController extends Controller
{
    public function __construct(
        public readonly BusinessDateRepositoryInterface $businessDateRepo,
        public readonly CashRegisterRepositoryInterface $cashRegisterRepo,
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly UserServiceInterface $userServ,
        public readonly UserRepositoryInterface $userRepo,
    ) {
    }

    public function store(OpeningPreparationRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($request->business_date['store_id']);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => [
                    ['ストア情報の読み込みができませんでした']
                ]
            ], 404);
        }

        $hasPermission = $this->userServ->hasStorePermission(
            $request->user(),
            $store,
            Permission::PERMISSIONS['OPERATION_UNDER_STORE_DASHBOARD']['id']
        );

        if (!$hasPermission) {
            return response()->json([
                'status' => 'failure',
                'errors' => [
                    ['この操作を実行する権限がありません']
                ]
            ], 403);
        }

        // トランザクションを開始する
        DB::beginTransaction();

        try {
            // 営業日付の登録
            // 年、月、日を取得
            $year = intval($request->input('business_date.business_date_year'));
            $month = intval($request->input('business_date.business_date_month'));
            $day = intval($request->input('business_date.business_date_day'));

            // Dateオブジェクトを作成
            $requestBusinessDate = Carbon::createFromDate($year, $month, $day);

            $businessDateArray = [
                ...$request->business_date,
                'business_date' => $requestBusinessDate
            ];
            $businessDate = $this->businessDateRepo->createBusinessDate($businessDateArray);

            // 釣銭準備金の登録
            $this->cashRegisterRepo->createCashRegister($businessDate, $request->cash_register);

            DB::commit();
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバックする
            DB::rollback();
            // ログの出力
            CustomLog::error($e);

            return response()->json([
                'status' => 'failure',
                'errors' => [
                    [$e->getMessage()]
                ]
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'messages' => ['営業を開始しました。'],
            'data' => [
                'businessDate' => $businessDate->business_date
            ]
        ], 200);
    }
}
