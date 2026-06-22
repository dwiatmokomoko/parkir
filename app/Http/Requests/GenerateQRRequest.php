<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateQRRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_type' => 'required|in:motorcycle,car',
            'license_plate' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9 .-]+$/'],
            'attendant_id' => 'required|integer|exists:parking_attendants,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'vehicle_type.required' => 'Jenis kendaraan harus diisi',
            'vehicle_type.in' => 'Jenis kendaraan harus motorcycle atau car',
            'license_plate.required' => 'Plat nomor harus diisi',
            'license_plate.max' => 'Plat nomor maksimal 20 karakter',
            'license_plate.regex' => 'Plat nomor hanya boleh berisi huruf, angka, spasi, titik, dan strip',
            'attendant_id.required' => 'ID juru parkir harus diisi',
            'attendant_id.integer' => 'ID juru parkir harus berupa angka',
            'attendant_id.exists' => 'Juru parkir tidak ditemukan',
        ];
    }
}
