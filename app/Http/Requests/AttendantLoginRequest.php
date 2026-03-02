<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendantLoginRequest extends FormRequest
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
            'registration_number' => ['required', 'string', 'max:50'],
            'pin' => ['required', 'string', 'min:4'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'registration_number.required' => 'Nomor registrasi wajib diisi.',
            'registration_number.max' => 'Nomor registrasi maksimal 50 karakter.',
            'pin.required' => 'PIN wajib diisi.',
            'pin.min' => 'PIN minimal 4 karakter.',
        ];
    }
}
