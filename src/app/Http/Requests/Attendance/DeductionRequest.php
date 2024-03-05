<?php

namespace App\Http\Requests\Attendance;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DeductionRequest extends BaseFormRequest
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
            'attendanceIdentifier.store_id' => 'required|numeric',
            'attendanceIdentifier.user_id' => 'required|numeric',
            'deduction.name' => 'required|string',
            'deduction.amount' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'attendanceIdentifier.store_id.required' => parent::UPDATE_SCREEN_MESSAGE,
            'attendanceIdentifier.store_id.numeric' => parent::UPDATE_SCREEN_MESSAGE,
            'attendanceIdentifier.user_id.required' => parent::UPDATE_SCREEN_MESSAGE,
            'attendanceIdentifier.user_id.numeric' => parent::UPDATE_SCREEN_MESSAGE,

            'deduction.name.required' => parent::UPDATE_SCREEN_MESSAGE,
            'deduction.name.string' => parent::UPDATE_SCREEN_MESSAGE,
            'deduction.amount.required' => parent::UPDATE_SCREEN_MESSAGE,
            'deduction.amount.numeric' => parent::UPDATE_SCREEN_MESSAGE,
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
