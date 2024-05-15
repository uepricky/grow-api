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
    BillController,
    RollController,
    OrderController,
    BillPaymentController,
    ExtensionSetController,
    ClosingStoreController,
    DeductionController,
    BusinessDateController,
    StoreReportController,
    StoreRoleController,
    UserIncentiveController,
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

Route::get('/healthCheck', function () {
    return response()->json(['status' => 'OK'], 200);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'getLoggedInUser'])->name('user.getLoggedInUser');

    /*************************
     * スタッフダッシュボード用
     * ユーザーの所属している店舗一覧を返す
     *************************/
    Route::get('/userStores', [StoreController::class, 'getUserStores'])->name('stores.userStores');

    /********************************
     * グループもしくはストア権限あり※ストア権限とグループ権限に2つのAPIを定義して削除
     * ※ストア権限チェックのため、パラメタstoreId必須
     ********************************/
    Route::middleware(['hasGroupOrStorePermission'])->group(function () {
        Route::prefix('/stores')->group(function () {
            Route::prefix('/{storeId}')->group(function () {
                // ここに記載
                Route::get('/', [StoreController::class, 'get'])->name('stores.get');
            });
        });
    });

    /*************************
     * ストア権限あり
     *************************/
    Route::middleware(['hasStorePermission'])->group(function () {
        Route::prefix('/stores')->group(function () {

            Route::prefix('/{storeId}')->group(function () {

                /****************************
                 * システムマスタ関連ここから
                 ***************************/
                // システムメニューカテゴリ一覧を取得
                Route::get('/sysMenuCategories', [SysMenuCategoryController::class, 'getAll']);

                // システム支払いカテゴリ
                Route::get('/sysPaymentMethods', [SysPaymentMethodController::class, 'getAll']);


                /****************************
                 * ストアマスタ関連ここから
                 ***************************/
                // メニューカテゴリー
                Route::prefix('/menuCategories')->group(function () {
                    Route::get('/', [MenuCategoryController::class, 'getAll']);
                    Route::post('/', [MenuCategoryController::class, 'store']);
                    Route::get('/{menuCategoriyId}', [MenuCategoryController::class, 'get']);
                    Route::put('/{menuCategoriyId}', [MenuCategoryController::class, 'update']);
                    Route::delete('/{menuCategoriyId}', [MenuCategoryController::class, 'archive']);
                });

                // メニュー
                Route::prefix('/menus')->group(function () {
                    Route::get('/', [MenuController::class, 'getAll']);
                    Route::post('/', [MenuController::class, 'store']);
                    Route::get('/{menuId}', [MenuController::class, 'get']);
                    Route::put('/{menuId}', [MenuController::class, 'update']);
                    Route::delete('/{menuId}', [MenuController::class, 'archive']);
                });

                // セットメニュー
                Route::prefix('/setMenus')->group(function () {
                    Route::get('/', [SetMenuController::class, 'getAll']);
                    Route::post('/', [SetMenuController::class, 'store']);
                    Route::get('/{setMenuId}', [SetMenuController::class, 'get']);
                    Route::put('/{setMenuId}', [SetMenuController::class, 'update']);
                    Route::delete('/{setMenuId}', [MenuController::class, 'archive']);
                });

                // 指名メニュー
                Route::prefix('/selectionMenus')->group(function () {
                    Route::get('/', [SelectionMenuController::class, 'getAll']);
                    Route::post('/', [SelectionMenuController::class, 'store']);
                    Route::get('/{selectionMenuId}', [SelectionMenuController::class, 'get']);
                    Route::put('/{selectionMenuId}', [SelectionMenuController::class, 'update']);
                    Route::delete('/{selectionMenuId}', [SelectionMenuController::class, 'archive']);
                });

                // 卓マスタ
                Route::prefix('/tables')->group(function () {
                    Route::get('/', [TableController::class, 'getAll']);
                    Route::post('/', [TableController::class, 'store']);
                    Route::get('/{tableId}', [TableController::class, 'get']);
                    Route::put('/{tableId}', [TableController::class, 'update']);
                    Route::delete('/{tableId}', [TableController::class, 'archive']);
                });

                // 支払い方法マスタ
                Route::prefix('/paymentMethods')->group(function () {
                    Route::get('/', [PaymentMethodController::class, 'getAll']);
                    Route::post('/', [PaymentMethodController::class, 'store']);
                    Route::get('/{paymentMethodId}', [PaymentMethodController::class, 'get']);
                    Route::put('/{paymentMethodId}', [PaymentMethodController::class, 'update']);
                    Route::delete('/{paymentMethodId}', [PaymentMethodController::class, 'archive']);
                });

                // ストアロール
                // TODO: 重複しているため、マージ
                Route::prefix('/storeRoles')->group(function () {
                    Route::get('/', [StoreRoleController::class, 'getAll']);
                });

                // TODO: 重複しているため、マージ
                // apiはこちら、コントローラは、StoreRoleControllerが適切かな
                Route::prefix('/rolls')->group(function () {
                    Route::get('/', [RollController::class, 'getStoreRoles']);
                });

                // 店舗レポート
                Route::prefix('/storeReports')->group(function () {
                    Route::get('/{yearMonth}', [StoreReportController::class, 'get']);
                });

                // 店舗に属するユーザー一覧
                Route::prefix('/storeUsers')->group(function () {
                    Route::get('/{storeRoleId}', [UserController::class, 'getStoreRoleUsers']);
                });

                // ユーザーインセンティブ
                Route::prefix('/userIncentives')->group(function () {
                    Route::get('/{yearMonth}', [UserIncentiveController::class, 'get']);
                });

                // 勤怠情報
                Route::prefix('/attendances')->group(function () {
                    Route::get('/{yearMonth}/{storeRoleId}', [AttendanceController::class, 'getSpecifiedPeriodAttendances']);
                });

                /****************************
                 * ストアマスタ関連ここまで
                 ***************************/

                // 開店準備
                Route::prefix('/openingPreparation')->group(function () {
                    Route::post('/', [OpeningPreparationController::class, 'store']);
                });

                /****************************
                 * 個別営業日関連
                 ***************************/
                Route::prefix('/businessDate')->group(function () {
                    // 営業日
                    Route::get('/', [BusinessDateController::class, 'getCurrentBusinessDate']);

                    Route::prefix('/{businessDate}')->group(function () {
                        // 勤怠
                        Route::prefix('/attendances')->group(function () {
                            Route::get('/', [AttendanceController::class, 'get']);
                            Route::put('/bulkUpdate', [AttendanceController::class, 'bulkUpdate']);
                            Route::put('/updateTardyAbsence', [AttendanceController::class, 'updateTardyAbsence'])->name('attendances.update-tardy-absence');
                            Route::put('/updatePayrollPayment', [AttendanceController::class, 'updatePayrollPayment'])->name('attendances.update-payroll-payment');
                        });

                        // 控除
                        Route::prefix('/deductions')->group(function () {
                            Route::get('/users/{user_id}', [DeductionController::class, 'get']);
                            Route::post('/', [DeductionController::class, 'updateOrInsert']);
                        });

                        // 伝票
                        Route::prefix('/bills')->group(function () {
                            Route::get('/', [BillController::class, 'getAll']);
                            Route::get('/{billId}', [BillController::class, 'get']);
                            Route::post('/', [BillController::class, 'store'])->name('bills.store');
                        });

                        // 延長セット
                        Route::prefix('/extension-sets')->group(function () {
                            Route::post('/', [ExtensionSetController::class, 'store'])->name('extension-sets.store');
                        });

                        // オーダー
                        Route::prefix('/orders')->group(function () {
                            Route::post('/', [OrderController::class, 'store']);
                            Route::put('/', [OrderController::class, 'update']);
                            Route::delete('/', [OrderController::class, 'archive']);
                        });

                        // 会計
                        Route::prefix('/payments')->group(function () {
                            Route::post('/', [BillPaymentController::class, 'store'])->name('bill-payments.store');
                            Route::delete('/{billId}', [BillPaymentController::class, 'cancel'])->name('bill-payments.cancel');
                        });

                        // 退店
                        Route::put('/departure/{billId}', [BillController::class, 'departure'])->name('bills.departure');

                        // billIdに関するAPIはprefix{billId}にまとめるを考える

                        // 閉店準備
                        Route::prefix('/closing')->group(function () {
                            Route::get('/preparation', [ClosingStoreController::class, 'preparation'])->name('closing.preparation');
                            Route::post('/register', [ClosingStoreController::class, 'register'])->name('closing.register');
                        });
                    });
                });
            });
        });
    });

    /**************************
     * グループ権限あり
     *************************/
    Route::middleware(['hasGroupPermission'])->group(function () {
        Route::prefix('/groups')->group(function () {
            Route::prefix('/{groupId}')->group(function () {
                // ユーザー
                Route::prefix('/users')->group(function () {
                    Route::get('/', [UserController::class, 'index'])->name('users.index');
                    Route::post('/', [UserController::class, 'store'])->name('users.store');
                    Route::get('/{userId}', [UserController::class, 'get'])->name('users.get');
                    Route::put('/{userId}', [UserController::class, 'update'])->name('users.update');
                    Route::delete('/{userId}', [UserController::class, 'archive'])->name('users.archive');
                });

                // ロール
                Route::prefix('/roles')->group(function () {
                    Route::get('/', [GroupRoleController::class, 'index'])->name('groupRoles.index');
                });

                Route::get('/stores-with-roles', [GroupController::class, 'getStoresWithRoles']);

                // 店舗
                Route::prefix('/stores')->group(function () {
                    Route::get('/', [StoreController::class, 'index'])->name('stores.index');
                    Route::post('/', [StoreController::class, 'store'])->name('stores.store');

                    Route::prefix('/{storeId}')->group(function () {
                        Route::put('/', [StoreController::class, 'update'])->name('stores.update');
                    });
                });
            });
        });
    });

    /**********************
     * 契約者権限あり
     *********************/
    Route::middleware(['isContractor'])->group(function () {
        // サブスクリプション
        Route::prefix('/subscriptions')->group(function () {
            Route::get('/setupIntent', [SubscriptionController::class, 'getSetupIntent']);
            Route::get('/paymentMethod', [SubscriptionController::class, 'getPaymentMethod']);
            Route::post('/', [SubscriptionController::class, 'create']);
        });

        // 店舗
        Route::prefix('/stores')->group(function () {
            Route::prefix('/{storeId}')->group(function () {
                // サブスクリプション
                Route::prefix('/subscriptions')->group(function () {
                    Route::get('/', [SubscriptionController::class, 'getSubscriptionStatus']);
                    Route::delete('/', [SubscriptionController::class, 'cancelSubscription']);
                });
            });
        });
    });
});
