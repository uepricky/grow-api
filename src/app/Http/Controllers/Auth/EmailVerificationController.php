<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function verify(int $userId, string $hash, Request $request)
    {
        $frontendUrl = config('app.frontend_url') . "/register-complete?id={$userId}&hash={$hash}";

        // 署名の検証
        if (!URL::hasValidSignature($request, true)) {
            // 署名が無効な場合はエラーを返す
            $errorUrl = $frontendUrl . '&error=invalid_signature';
            return redirect()->away($errorUrl);
        }

        // ユーザーの取得
        $user = User::find($userId);
        if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            $errorUrl = $frontendUrl . '&error=user_not_found_or_hash_mismatch';
            return redirect()->away($errorUrl);
        }

        // メールが未認証の場合、認証済みに更新
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->away($frontendUrl);
    }
}
