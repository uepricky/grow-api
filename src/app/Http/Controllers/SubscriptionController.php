<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Subscription;
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface
};

class SubscriptionController extends Controller
{
    public function __construct(
        public readonly StoreRepositoryInterface $storeRepo,
    ) {
    }

    public function getSetupIntent()
    {
        // 契約済の場合、エラーメッセージ
        $intent = auth()->user()->createSetupIntent();

        return response()->json([
            'status' => 'success',
            'data' => $intent
        ], 200);
    }

    public function create(Request $request)
    {
        $newSubscription = $request->user()->newSubscription(
            'standard',
            env('STANDARD_SUBSCRIPTION', false),
        )->create($request->paymentMethodId);

        // storeのsubscription_idに登録
        $store = $this->storeRepo->findStore($request->storeId);
        $store->subscription_id = $newSubscription->id;
        $this->storeRepo->updateStore($store, $store->toArray());

        return response()->json([
            'status' => 'success',
            'data' => $newSubscription
        ], 200);
    }

    public function getSubscriptionStatus(int $storeId)
    {
        $store = $this->storeRepo->findStore($storeId);

        return response()->json([
            'status' => 'success',
            'data' => $store->subscription
        ], 200);

        // $subscriptionStatus = $store->subscription;

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $subscriptionStatus
        // ], 200);


        // try {
        //     // ストアインスタンスからサブスクリプションを取得する
        //     $subscription = $store->subscription();

        //     if ($subscription && $subscription->active()) {
        //         // サブスクリプションが有効な場合
        //         return response()->json([
        //             'status' => 'active',
        //             'ends_at' => $subscription->ends_at
        //         ]);
        //     } elseif ($subscription && $subscription->onGracePeriod()) {
        //         // 猶予期間中の場合
        //         return response()->json([
        //             'status' => 'grace_period',
        //             'ends_at' => $subscription->ends_at
        //         ]);
        //     } else {
        //         // サブスクリプションが無効または期限切れの場合
        //         return response()->json([
        //             'status' => 'inactive'
        //         ]);
        //     }
        // } catch (IncompletePayment $exception) {
        //     // 支払いが完了していない場合
        //     return response()->json([
        //         'status' => 'incomplete_payment'
        //     ], 402);
        // } catch (\Exception $exception) {
        //     // その他の例外
        //     return response()->json([
        //         'error' => $exception->getMessage()
        //     ], 500);
        // }
    }
}
