<?php

namespace App\Http\Requests\Attendance;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class PayrollPaymentRequest extends BaseFormRequest
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
            'store_id' => 'required|numeric',
            'business_date_id' => 'required|numeric',
            'attendances.user_id' => 'required|numeric',
            'attendances.payment_type' => 'required|numeric',
            'attendances.payment_amount' => 'required|numeric',
            'attendances.payment_source' => 'nullable|bool',
        ];
    }

    public function messages()
    {
        return [
            'store_id.required' => parent::UPDATE_SCREEN_MESSAGE,
            'store_id.numeric' => parent::UPDATE_SCREEN_MESSAGE,
            'business_date_id.required' => parent::UPDATE_SCREEN_MESSAGE,
            'business_date_id.numeric' => parent::UPDATE_SCREEN_MESSAGE,
            'attendances.user_id.required' => parent::UPDATE_SCREEN_MESSAGE,
            'attendances.user_id.numeric' => parent::UPDATE_SCREEN_MESSAGE,
            'attendances.payment_type.required' => '支給種別は選択必須です。',
            'attendances.payment_amount.required' => '金額は必須項目です。',
            'attendances.payment_amount.numeric' => '金額は数値で入力してください。',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        throw new HttpResponseException(response()->json([
            'status' => 'failure',
            'errors' => $errors
        ], 400));
    }
}
