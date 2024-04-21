<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\{
    GroupRepository\GroupRepositoryInterface,
};
use App\Services\UserService\UserServiceInterface;
use App\Models\{
    Permission
};

class HasGroupPermission
{
    public function __construct(
        public readonly GroupRepositoryInterface $groupRepo,
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
        // 権限チェック
        $hasPermission = $this->userServ->hasGroupPermission(
            $request->user(),
            Permission::PERMISSIONS['OPERATION_UNDER_GROUP_DASHBOARD']['id']
        );
        if (!$hasPermission) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['この操作を実行する権限がありません']
            ], 403);
        }

        return $next($request);
    }
}
