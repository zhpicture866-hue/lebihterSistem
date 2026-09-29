<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sesuaikan dengan gate/permission jika perlu
    }

    public function rules(): array
    {
        return [
            'project_name'      => 'required|string|max:255',
            'project_location'  => 'nullable|string',
            'province_id'       => 'nullable|integer',
            'city_id'           => 'nullable|integer',
            'district_id'       => 'nullable|integer',
            'sub_district_id'   => 'nullable|integer',
            'postal_code_id'    => 'nullable|integer',
            'employee_id'       => 'required|uuid',
            'customer_id'       => 'required|uuid',
            'affiliator_id'     => 'nullable|uuid',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'project_status'    => 'nullable|integer',
            'description'    => 'nullable|string',
            'base_url'  => 'nullable', 'url', 'max:255',
            'project_type' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = \DB::table('lebihtersistem.project_types')->where('id', $value)->exists();
                    if (! $exists) {
                        $fail('Jenis proyek tidak valid.');
                    }
                },
            ],
        ];
    }
}
