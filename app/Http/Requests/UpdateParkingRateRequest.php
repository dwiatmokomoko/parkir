<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParkingRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the admin middleware
        // This request is only accessible to authenticated admins
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_type' => 'required|in:motorcycle,car',
            'street_section' => 'nullable|string|max:255',
            'rate' => 'required|numeric|min:0.01',
            'effective_from' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:now',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'rate.min' => 'Parking rate must be a positive value',
            'rate.numeric' => 'Parking rate must be a valid number',
            'vehicle_type.in' => 'Vehicle type must be either motorcycle or car',
            'effective_from.after_or_equal' => 'Effective date must be in the future or now',
        ];
    }
}
