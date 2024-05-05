<?php

namespace App\Http\Requests\OpeningPreparation;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class OpeningPreparationRequest extends BaseFormRequest
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
            'business_date.store_id' => 'required|numeric',
            // 'business_date.business_date' => 'required|date',

            'business_date.business_date_year' => 'required|string',
            'business_date.business_date_month' => 'required|string',
            'business_date.business_date_day' => 'required|string',

            'cash_register.cash_at_opening' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'business_date.store_id.required' => parent::UPDATE_SCREEN_MESSAGE,
            'business_date.store_id.numeric' => parent::UPDATE_SCREEN_MESSAGE,

            // 'business_date.business_date.required' => '営業日付は必須項目です。',
            // 'business_date.business_date.date' => parent::UPDATE_SCREEN_MESSAGE,

            'business_date.business_date_year.required' => '営業年は必須項目です。',
            'business_date.business_date_year.string' => parent::UPDATE_SCREEN_MESSAGE,
            'business_date.business_date_month.required' => '営業月は必須項目です。',
            'business_date.business_date_month.string' => parent::UPDATE_SCREEN_MESSAGE,
            'business_date.business_date_day.required' => '営業日は必須項目です。',
            'business_date.business_date_day.string' => parent::UPDATE_SCREEN_MESSAGE,

            'cash_register.cash_at_opening.required' => '釣銭準備金は必須です。',
            'cash_register.cash_at_opening.integer' => parent::UPDATE_SCREEN_MESSAGE,
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
