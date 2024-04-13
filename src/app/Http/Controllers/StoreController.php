<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Store\StoreRequest;
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface,
    StoreDetailRepository\StoreDetailRepositoryInterface
};
use App\Services\{
    StoreService\StoreServiceInterface,
};
use App\Models\Store;
use App\Log\CustomLog;

class StoreController extends Controller
{
    public function __construct(
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly StoreDetailRepositoryInterface $storeDetailRepo,
        public readonly StoreServiceInterface $storeServ,
    ) {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        // トランザクションを開始する
        DB::beginTransaction();
        try {

            // グループIDを付与
            $group_id = auth()->user()->groups->first()->id;

            // 店舗データを取得
            $storeData = $request->store;

            // 'group_id' キーで $group_id を追加
            $storeData['group_id'] = $group_id;

            // 店舗作成
            $this->storeServ->createStore(
                $storeData,
                $request->store_detail,
            );

            DB::commit();
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバックする
            DB::rollback();

            // ログの出力
            CustomLog::error($e);

            return response()->json([
                'status' => 'failure',
                'errors' => ['新規店舗の登録に失敗しました。']
            ], 500);
        }
        return response()->json([
            'status' => 'success',
            'data' => [],
            'messages' => ['新規店舗の登録が完了しました。']
        ], 200);
    }

    public function get(int $id)
    {
        $store = $this->storeRepo->findStore($id);
        $storeDetail = $this->storeDetailRepo->getLatestStoreDetail($store);

        // 以下用途不明
        // $nonEffectiveStoreDetails = $this->storeDetailRepo->getNonEffectiveStoreDetails($store);

        return response()->json([
            'status' => 'success',
            'data' => [
                'store' => $store,
                'storeDetail' => $storeDetail
            ],
            'messages' => ['店舗情報を取得しました。']
        ], 200);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, int $id)
    {
        $store = $this->storeRepo->findStore($id);

        // グループIDを付与
        $group_id = auth()->user()->groups->first()->id;

        // 店舗データを取得
        $storeData = $request->store;

        // 'group_id' キーで $group_id を追加
        $storeData['group_id'] = $group_id;

        // トランザクションを開始する
        DB::beginTransaction();
        try {
            // 店舗の更新
            $this->storeRepo->updateStore($store, $storeData);

            // 店舗詳細の作成
            $this->storeDetailRepo->createStoreDetail($store, $request->store_detail);

            DB::commit();
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバックする
            DB::rollback();

            // ログの出力
            CustomLog::error($e);

            abort(500);
        }

        return response()->json([
            'status' => 'success',
            'data' => [],
            'messages' => ['店舗の編集が完了しました。']
        ], 200);
    }
}
