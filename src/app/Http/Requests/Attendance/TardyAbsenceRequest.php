<?php

namespace App\Http\Requests\Attendance;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class TardyAbsenceRequest extends BaseFormRequest
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
            'attendances.tardy_absence_type' => 'required|numeric',
            'attendances.late_total_minute' => 'required|numeric',
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
            'attendances.tardy_absence_type.required' => '遅刻/欠勤種別は選択必須です。',
            'attendances.late_total_minute.required' => '休憩時間は必須項目です。',
            'attendances.late_total_minute.numeric' => '休憩時間は数値で入力してください。',
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
