<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Log\CustomLog;
use App\Http\Requests\{
    Table\TableRequest,
    StoreIdRequest
};
use App\Models\{
    Permission,
    Table
};
use App\Repositories\{
    TableRepository\TableRepositoryInterface,
    StoreRepository\StoreRepositoryInterface
};
use App\Services\UserService\UserServiceInterface;

use Illuminate\Auth\Access\AuthorizationException;

class TableController extends Controller
{
    public function __construct(
        public readonly TableRepositoryInterface $tableRepo,
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

        // 卓を取得
        $tables = $this->tableRepo->getAllTables($store);

        return response()->json([
            'status' => 'success',
            'data' => $tables
        ], 200);
    }

    public function store(int $storeId, TableRequest $request)
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
        $table = $this->tableRepo->createTable($request->table);

        return response()->json([
            'status' => 'success'
        ], 200);
    }

    public function get(int $storeId, int $tableId)
    {
        // 卓の取得
        $table = $this->tableRepo->find($tableId);
        if (is_null($table)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['卓情報情報の読み込みができませんでした']
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
            'data' => $table
        ], 200);
    }

    public function update(TableRequest $request, int $storeId, int $tableId)
    {
        // 卓の取得
        $table = $this->tableRepo->find($tableId);
        if (is_null($table)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['卓情報情報の読み込みができませんでした']
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
            // 既存のテーブルを論理削除
            $this->tableRepo->softDeleteTable($table);

            // テーブル作成
            $table = $this->tableRepo->createTable($request->table);

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

    public function archive(int $storeId, int $tableId)
    {
        // 卓の取得
        $table = $this->tableRepo->find($tableId);
        if (is_null($table)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['卓情報情報の読み込みができませんでした']
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

        $this->tableRepo->softDeleteTable($table);

        return response()->json([
            'status' => 'success',
            'data' => []
        ], 200);
    }
}
