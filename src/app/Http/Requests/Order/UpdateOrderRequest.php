<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseFormRequest;

class UpdateOrderRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'itemized_order_id' => 'required|integer',
            'sys_menu_category_id' => 'required|integer',
            'order_ids' => 'required|array',
            'quantity' => 'required|integer|min:0',
            'order' => 'required|array',
            'order.adjust_amount' => 'required|integer|min:0',
            'user_incentive' => 'nullable|array',
            'user_incentive.amount' => 'nullable|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'itemized_order_id.required' => parent::UPDATE_SCREEN_MESSAGE,
            'itemized_order_id.integer' => parent::UPDATE_SCREEN_MESSAGE,
            'sys_menu_category_id.required' => parent::UPDATE_SCREEN_MESSAGE,
            'sys_menu_category_id.integer' => parent::UPDATE_SCREEN_MESSAGE,
            'order_ids.required' => parent::UPDATE_SCREEN_MESSAGE,
            'order_ids.array' => parent::UPDATE_SCREEN_MESSAGE,
            'quantity.required' => '数量は必ず指定してください',
            'quantity.integer' => '数量は整数で指定してください',
            'quantity.min' => '数量は0以上の整数で指定してください',
            'order.required' => parent::UPDATE_SCREEN_MESSAGE,
            'order.array' => parent::UPDATE_SCREEN_MESSAGE,
            'order.adjust_amount.required' => '単価は必ず指定してください',
            'order.adjust_amount.integer' => '単価は整数で指定してください',
            'order.adjust_amount.min' => '単価は0以上の整数で指定してください',
            'user_incentive.array' => parent::UPDATE_SCREEN_MESSAGE,
            'user_incentive.amount.integer' => 'バック金額は整数で指定してください',
            'user_incentive.amount.min' => 'バック金額は0以上の整数で指定してください',
        ];
    }
}
