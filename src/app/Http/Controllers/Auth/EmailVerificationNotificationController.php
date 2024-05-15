<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = User::find($request->id);

        if (!$user || !hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'status' => 'failure',
                'errors' => [
                    ['ユーザー情報が一致しません。']
                ]
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'messages' => ['すでに認証済みのユーザーです。'],
                'data' => []
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => 'success',
            'messages' => ['認証メールを再送信いたしました。'],
            'data' => []
        ], 200);
    }
}
