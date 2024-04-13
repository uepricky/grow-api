<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    GroupController,
    StoreController,
    UserController,
    GroupRoleController,
    SysMenuCategoryController,
    MenuCategoryController,
    MenuController,
    SetMenuController,
    SelectionMenuController,
    TableController,
    PaymentMethodController,
    SysPaymentMethodController,
    OpeningPreparationController,
    AttendanceController,
    HallController,
    BillController,
    RollController,
    OrderController,
    BillPaymentController,
    ExtensionSetController,
    ClosingStoreController,
    DeductionController,
    BusinessDateController,
    SubscriptionController,
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'getLoggedInUser'])->name('user.getLoggedInUser');

    /**************************
     * グループ
     *************************/
    // Route::prefix('/groups')->group(function () {
    // });

    /**************************
     * ストア
     *************************/
    Route::middleware(['hasStorePermission'])->group(function () {
        Route::prefix('/stores')->group(function () {
            Route::post('/', [StoreController::class, 'store'])->name('stores.store');

            Route::prefix('/{storeId}')->group(function () {
                Route::get('/', [StoreController::class, 'get'])->name('stores.get');
                // パス通っていないため、個別確認
                Route::put('/', [StoreController::class, 'update'])->name('stores.update');

                // メニューカテゴリー
                Route::prefix('/menuCategories')->group(function () {
                    Route::get('/', [MenuCategoryController::class, 'getAll']);
                    Route::post('/', [MenuCategoryController::class, 'store']);
                    Route::get('/{menuCategoriyId}', [MenuCategoryController::class, 'get']);
                    Route::put('/{menuCategoriyId}', [MenuCategoryController::class, 'update']);
                    Route::delete('/{menuCategoriyId}', [MenuCategoryController::class, 'archive']);
                });
            });
        });
    });





    /**
     * 以下既存ソースすべてここより上に引っ越し
     */

    // 店舗
    Route::prefix('/stores')->group(function () {

        //     Route::post('/', [StoreController::class, 'store'])->name('stores.store');
        //     Route::get('/{id}', [StoreController::class, 'get'])->name('stores.get');
        //     // Route::put('/{id}', [StoreController::class, 'update'])->name('stores.update');

        //     Route::delete('/{id}', [StoreController::class, 'archive'])->name('stores.archive');

        // 特定の店舗に属する各種情報についてAPI
        Route::prefix('/{storeId}')->group(function () {
            // サブスクリプション
            Route::prefix('/subscriptions')->group(function () {
                Route::get('/', [SubscriptionController::class, 'getSubscriptionStatus']);
            });
        });
    });

    // ユーザー
    Route::prefix('/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{id}', [UserController::class, 'get'])->name('users.get');
        Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'archive'])->name('users.archive');
        Route::get('/{id}/permissions', [UserController::class, 'getUserPermissions'])->name('users.getUserPermissions');
        Route::get('/report/from/{businessDateFrom}/to/{businessDateTo}', [UserController::class, 'reportIndex'])->name('usersReport.index');
    });

    // サブスクリプション
    Route::prefix('/subscriptions')->group(function () {
        Route::get('/setupIntent', [SubscriptionController::class, 'getSetupIntent']);
        Route::get('/paymentMethod', [SubscriptionController::class, 'getPaymentMethod']);
        Route::post('/', [SubscriptionController::class, 'create']);
    });

    // グループ
    Route::prefix('/groups')->group(function () {
        Route::get('/{group_id}/roles', [GroupRoleController::class, 'index']);
        Route::get('/{group_id}/stores-with-roles', [GroupController::class, 'getStoresWithRoles']);
    });

    // グループロール
    Route::prefix('/groupRoles')->group(function () {
        Route::get('/', [GroupRoleController::class, 'index'])->name('groupRoles.index');
    });

    Route::get('/group/stores', [GroupController::class, 'getStores']);

    // システムメニューカテゴリ一覧を取得
    Route::get('/sysMenuCategories', [SysMenuCategoryController::class, 'getAll']);

    // システム支払いカテゴリ
    Route::get('/sysPaymentMethods', [SysPaymentMethodController::class, 'getAll']);



    // メニューカテゴリー
    // Route::prefix('/menuCategories')->group(function () {
    //     Route::get('/', [MenuCategoryController::class, 'getAll']);
    //     Route::post('/', [MenuCategoryController::class, 'store']);
    //     Route::get('/{id}', [MenuCategoryController::class, 'get']);
    //     Route::put('/{id}', [MenuCategoryController::class, 'update']);
    //     Route::delete('/{id}', [MenuCategoryController::class, 'archive']);
    // });

    // メニュー
    Route::prefix('/menus')->group(function () {
        Route::get('/', [MenuController::class, 'getAll']);
        Route::post('/', [MenuController::class, 'store']);
        Route::get('/{id}', [MenuController::class, 'get']);
        Route::put('/{id}', [MenuController::class, 'update']);
        Route::delete('/{id}', [MenuController::class, 'archive']);
    });

    // セットメニュー
    Route::prefix('/setMenus')->group(function () {
        Route::get('/', [SetMenuController::class, 'getAll']);
        Route::post('/', [SetMenuController::class, 'store']);
        Route::get('/{id}', [SetMenuController::class, 'get']);
        Route::put('/{id}', [SetMenuController::class, 'update']);
        Route::delete('/{id}', [MenuController::class, 'archive']);
    });

    // 指名メニュー
    Route::prefix('/selectionMenus')->group(function () {
        Route::get('/', [SelectionMenuController::class, 'getAll']);
        Route::post('/', [SelectionMenuController::class, 'store']);
        Route::get('/{id}', [SelectionMenuController::class, 'get']);
        Route::put('/{id}', [SelectionMenuController::class, 'update']);
        Route::delete('/{id}', [SelectionMenuController::class, 'archive']);
    });

    // 卓マスタ
    Route::prefix('/tables')->group(function () {
        Route::get('/', [TableController::class, 'getAll']);
        Route::post('/', [TableController::class, 'store']);
        Route::get('/{id}', [TableController::class, 'get']);
        Route::put('/{id}', [TableController::class, 'update']);
        Route::delete('/{id}', [TableController::class, 'archive']);
    });

    // 支払い方法マスタ
    Route::prefix('/paymentMethods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'getAll']);
        Route::post('/', [PaymentMethodController::class, 'store']);
        Route::get('/{id}', [PaymentMethodController::class, 'get']);
        Route::put('/{id}', [PaymentMethodController::class, 'update']);
        Route::delete('/{id}', [PaymentMethodController::class, 'archive']);
    });

    // 開店準備
    Route::prefix('/openingPreparation')->group(function () {
        // Route::get('/', [OpeningPreparationController::class, 'get']);
        Route::post('/', [OpeningPreparationController::class, 'store']);
    });

    Route::prefix('/store/{storeId}')->group(function () {
        // 営業日
        Route::prefix('/businessDate')->group(function () {
            Route::get('/', [BusinessDateController::class, 'getCurrentBusinessDate']);
        });
    });

    // 勤怠
    Route::prefix('/attendances')->group(function () {
        Route::get('/', [AttendanceController::class, 'get']);
        Route::put('/bulkUpdate', [AttendanceController::class, 'bulkUpdate']);
        Route::put('/updateTardyAbsence', [AttendanceController::class, 'updateTardyAbsence'])->name('attendances.update-tardy-absence');
        Route::put('/updatePayrollPayment', [AttendanceController::class, 'updatePayrollPayment'])->name('attendances.update-payroll-payment');
    });

    // 控除
    Route::prefix('/deductions')->group(function () {
        Route::post('/', [DeductionController::class, 'updateOrInsert']);
        Route::get('businessDate/{bisiness_date}/store/{store_id}/user/{user_id}', [DeductionController::class, 'get']);
    });



    // 伝票
    Route::prefix('/bills')->group(function () {
        Route::get('/{id}', [BillController::class, 'get']);
        Route::get('/', [BillController::class, 'getAll']);
        Route::post('/', [BillController::class, 'store'])->name('bills.store');
    });

    // Roll
    Route::prefix('/rolls')->group(function () {
        Route::get('/groupRoles', [RollController::class, 'getGroupRoles']);
        Route::get('/storeRoles', [RollController::class, 'getStoreRoles']);
    });

    // オーダー
    Route::prefix('/orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::put('/', [OrderController::class, 'update']);
        Route::delete('/', [OrderController::class, 'archive']);
    });

    // 延長セット
    Route::prefix('/extension-sets')->group(function () {
        Route::post('/', [ExtensionSetController::class, 'store'])->name('extension-sets.store');
    });

    // 会計
    Route::prefix('/payments')->group(function () {
        Route::post('/', [BillPaymentController::class, 'store'])->name('bill-payments.store');
        Route::delete('/{billId}', [BillPaymentController::class, 'cancel'])->name('bill-payments.cancel');
    });

    // 退店
    Route::put('/departure/{billId}', [BillController::class, 'departure'])->name('bills.departure');

    // 閉店準備
    Route::prefix('/closing')->group(function () {
        Route::get('/preparation', [ClosingStoreController::class, 'preparation'])->name('closing.preparation');
        Route::post('/register', [ClosingStoreController::class, 'register'])->name('closing.register');
    });

    // TODO: 以下いらないかも

    // ホール一覧
    Route::prefix('/halls')->group(function () {
        Route::get('/', [HallController::class, 'get']);
    });
});
