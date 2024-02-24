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
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Store::class);

        $group_id = auth()->user()->groups->first()->id;

        return view('store.create', compact('group_id'));
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

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(string $id)
    public function edit(Store $store)
    {
        $storeDetail = $this->storeDetailRepo->getLatestStoreDetail($store);

        $nonEffectiveStoreDetails = $this->storeDetailRepo->getNonEffectiveStoreDetails($store);

        return view('store.edit', compact('store', 'storeDetail', 'nonEffectiveStoreDetails'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Store $store)
    {
        $this->authorize('update', $store);

        // トランザクションを開始する
        DB::beginTransaction();
        try {
            // 店舗の更新
            $this->storeRepo->updateStore($store, $request->store);

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

        return redirect()->route('group-dashboard.home')->with('message', $store->name . 'の編集が完了しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
