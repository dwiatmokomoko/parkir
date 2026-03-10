<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateAttendantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->session()->has('admin_user_id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $attendantId = $this->route('id');

        return [
            'registration_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('parking_attendants', 'registration_number')->ignore($attendantId),
            ],
            'name' => 'sometimes|string|max:255',
            'street_section' => 'sometimes|string|max:255',
            'location_side' => 'nullable|string|max:50',
            'bank_account_number' => 'sometimes|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'pin' => [
                'sometimes',
                'string',
                'size:6',
                'regex:/^\d{6}$/', // Must be exactly 6 digits
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'registration_number.unique' => 'Nomor registrasi sudah terdaftar.',
            'pin.size' => 'PIN harus 6 digit.',
            'pin.regex' => 'PIN harus berupa 6 digit angka.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Don't hash here - hash after validation passes
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Hash the PIN after validation if provided
        if (is_array($validated) && isset($validated['pin'])) {
            $validated['pin'] = Hash::make($validated['pin']);
        }
        
        return $validated;
    }
}
