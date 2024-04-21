<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\{
    GroupRepository\GroupRepositoryInterface,
    StoreRepository\StoreRepositoryInterface,
};
use App\Services\UserService\UserServiceInterface;
use App\Models\{
    Permission
};

class HasGroupOrStorePermission
{
    public function __construct(
        public readonly GroupRepositoryInterface $groupRepo,
        public readonly StoreRepositoryInterface $storeRepo,
        public readonly UserServiceInterface $userServ,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // グループ権限チェック
        $hasPermission = $this->userServ->hasGroupPermission(
            $request->user(),
            Permission::PERMISSIONS['OPERATION_UNDER_GROUP_DASHBOARD']['id']
        );

        if ($hasPermission) return $next($request);

        // ストア権限チェック
        // ストアの取得
        $store = $this->storeRepo->findStore($request->storeId);
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

        if ($hasPermission) {
            return $next($request);
        } else {
            return response()->json([
                'status' => 'failure',
                'errors' => ['この操作を実行する権限がありません']
            ], 403);
        }
    }
}
