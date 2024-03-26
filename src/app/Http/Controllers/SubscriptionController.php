<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function getSetupIntent()
    {
        // 契約済の場合、エラーメッセージ
        $intent = auth()->user()->createSetupIntent();

        return response()->json([
            'status' => 'success',
            'data' => $intent
        ], 200);
    }
}
