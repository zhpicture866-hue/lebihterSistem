<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    return [
        'project_id' => 'required|uuid|exists:projects,id',

        'contact_name'  => 'nullable|string|max:255',
        'contact_phone' => 'nullable|string|max:50',
        'site_area'     => 'nullable|string|max:255',
        'building_area' => 'nullable|string|max:255',
        'notes'         => 'nullable|string',

        'survey_date' => 'required|date',
        'survey_time' => 'required|date_format:H:i',

        'employee_id'   => 'required|array|min:1',
        'employee_id.*' => 'uuid|exists:employees,id',
        'items'                 => 'required|array|min:1',
        'items.*.description'   => 'required|string|max:2000',
        'items.*.remark'        => 'nullable|string|max:2000',
        'documents'     => 'nullable|array',
        'documents.*'   => 'file|mimes:pdf|max:2048',
        'documentation'     => 'nullable|array',
        'documentation.*'   => 'image|mimes:jpg,jpeg,png|max:51240',
        'result_images'     => 'nullable|array',
        'result_images.*'   => 'image|mimes:jpg,jpeg,png|max:51240',
    ];
}

}
