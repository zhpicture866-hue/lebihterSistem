<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAccountingJournalRequest extends FormRequest
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
            // 'license_id' => [
            //     'sometimes',
            //     'exists:licenses,id',
            //     function ($attribute, $value, $fail) {
            //         $user = Auth::user();
            //         if ($user->hasRole('Akuntan')) {
            //             $licenses = $user->employee?->licenses;
            //             if (!$licenses || $licenses->isEmpty()) {
            //                 return $fail('Lisensi tidak ditemukan.');
            //             }
            //             if (!$licenses->pluck('id')->contains($value)) {
            //                 return $fail('Lisensi tidak valid.');
            //             }
            //         }
            //     }
            // ],
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            'details' => 'required|array',
            'details.*.account_id' => 'required|uuid|exists:accounting_accounts,id',
            'details.*.debit' => 'nullable|numeric',
            'details.*.credit' => 'nullable|numeric',
            'details.*.person' => 'nullable|string',
            'enclosure' => ['nullable', 'array'],
            'enclosure.*' => [
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'max:2048', // 2 MB per file
            ],
        ];
    }
}
