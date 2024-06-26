<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Stripe\Subscription;
use App\Repositories\{
    StoreRepository\StoreRepositoryInterface
};
use Illuminate\Support\Facades\DB;

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

    function getPaymentMethod()
    {
        return response()->json([
            'status' => 'success',
            'data' => auth()->user()
        ], 200);
    }

    function cancelSubscription(Request $request, int $storeId)
    {

        // return response()->json([
        //     'status' => 'success',
        //     'data' => auth()->user()
        // ], 200);

        // リクエストのPWが正しいことを確認
        $user = Auth::user();
        $pass = $user->password;
        if (!Hash::check($request->password, $pass)) {
            return response()->json([
                'status' => 'failure',
                'errors' => ['パスワードが異なります']
            ], 400);
        }

        // ユーザーの属する店舗であることを確認

        // 対象店舗の契約状態がactiveであることを確認

        $store = $this->storeRepo->findStore($storeId);
        $subscription = $store->subscription;
        // return response()->json([
        //     'status' => 'success',
        //     'data' => $subscription
        // ], 200);

        // キャンセル
        DB::beginTransaction();
        try {
            $resultSubscription = $subscription->cancel();
            // TODO: 本番では即時キャンセルしない
            // $resultSubscription = $subscription->cancelNow();

            DB::rollBack();
            return response()->json([
                'status' => 'success',
                'data' => $resultSubscription
            ], 200);

            if ($resultSubscription->stripe_status !== Subscription::STATUS_CANCELED) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failure',
                    'errors' => ['キャンセルに失敗しました。管理者にお問い合わせください。']
                ], 400);
            }

            // 店舗を削除
            $store->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'failure',
                'errors' => ['エラーが発生しました。もう一度お試しください。']
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'messages' => [$store->name . 'の解約・削除が完了しました。'],
            'data' => []
        ], 200);
    }
}
