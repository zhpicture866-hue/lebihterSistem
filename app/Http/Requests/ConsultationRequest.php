<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // atur permission sesuai kebutuhan
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|uuid|exists:projects,id',
            'employee_id' => 'required|uuid|exists:employees,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'site_area' => 'nullable|string|max:255',
            'building_area' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:2000',
            'items.*.remark' => 'nullable|string|max:2000',
            'documentation'     => 'nullable|array',
            'documentation.*'   => 'image|mimes:jpg,jpeg,png|max:51240',
            'documents'     => 'nullable|array',
            'documents.*'   => 'file|mimes:pdf|max:51240',
        ];
    }
}
