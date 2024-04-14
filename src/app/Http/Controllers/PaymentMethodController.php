<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;
use App\Http\Requests\{
    PaymentMethod\PaymentMethodRequest,
    StoreIdRequest
};
use App\Models\{
    Store,
    PaymentMethod,
    Permission
};
use App\Repositories\{
    PaymentMethodRepository\PaymentMethodRepositoryInterface,
    SysPaymentMethodCategoryRepository\SysPaymentMethodCategoryRepositoryInterface,
    StoreRepository\StoreRepositoryInterface
};
use Illuminate\Auth\Access\AuthorizationException;
use App\Services\UserService\UserServiceInterface;

class PaymentMethodController extends Controller
{
    public function __construct(
        public readonly PaymentMethodRepositoryInterface $paymentMethodRepo,
        public readonly SysPaymentMethodCategoryRepositoryInterface $sysPaymentMethodCategoryRepo,
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly UserServiceInterface $userServ,
    ) {
    }

    public function getAll(int $storeId)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);

        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // 支払い方法を取得
        $paymentMethods = $this->paymentMethodRepo->getStorePaymentMethods($store);

        return response()->json([
            'status' => 'success',
            'data' => $paymentMethods
        ], 200);
    }

    public function store(int $storeId, PaymentMethodRequest $request)
    {
        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // 新規登録
        $this->paymentMethodRepo->createPaymentMethod($request->payment_method);

        return response()->json([
            'status' => 'success'
        ], 200);
    }

    public function get(int $storeId, int $paymentMethodId)
    {
        // 支払い方法の取得
        $paymentMethod = $this->paymentMethodRepo->find($paymentMethodId);
        if (is_null($paymentMethod)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['支払い方法情報の読み込みができませんでした']
            ], 404);
        }

        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $paymentMethod
        ], 200);
    }

    public function update(PaymentMethodRequest $request, int $storeId, int $paymentMethodId)
    {
        // 支払い方法の取得
        $paymentMethod = $this->paymentMethodRepo->find($paymentMethodId);
        if (is_null($paymentMethod)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['支払い方法情報の読み込みができませんでした']
            ], 404);
        }

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
            // 現在のレコードを論理削除する
            $this->paymentMethodRepo->softDeletePaymentMethod($paymentMethod);

            // 新しいレコードを新規作成する
            $this->paymentMethodRepo->createPaymentMethod($request->payment_method);

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

    public function archive(int $storeId, int $paymentMethodId)
    {
        // 支払い方法の取得
        $paymentMethod = $this->paymentMethodRepo->find($paymentMethodId);
        if (is_null($paymentMethod)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['支払い方法情報の読み込みができませんでした']
            ], 404);
        }

        // ストアの取得
        $store = $this->storeRepo->findStore($storeId);
        if (is_null($store)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['ストア情報の読み込みができませんでした']
            ], 404);
        }

        // 現在のレコードを論理削除する
        $this->paymentMethodRepo->softDeletePaymentMethod($paymentMethod);

        return response()->json([
            'status' => 'success',
            'data' => []
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Store $store)
    {
        return view('payment_method.index', compact('paymentMethods', 'store'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Store $store)
    {
        // システム支払い方法一覧を取得
        $sysPaymentMethodCategories = $this->sysPaymentMethodCategoryRepo->getSysPaymentMethodCategories();

        return view('payment_method.create', compact('sysPaymentMethodCategories', 'store'));
    }

    /**
     * Store a newly created resource in storage.
     */


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Store $store, PaymentMethod $paymentMethod)
    {
        // システム支払い方法一覧を取得
        $sysPaymentMethodCategories = $this->sysPaymentMethodCategoryRepo->getSysPaymentMethodCategories();

        return view('payment_method.edit', compact('sysPaymentMethodCategories', 'paymentMethod', 'store'));
    }

    /**
     * Update the specified resource in storage.
     */
}
