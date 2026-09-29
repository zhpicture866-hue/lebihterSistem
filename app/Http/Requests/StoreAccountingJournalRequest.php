<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAccountingJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',

            'details' => 'required|array|min:1',

            'details.*.account_id' => 'required|uuid|exists:accounting_accounts,id',
            'details.*.debit' => 'nullable|numeric|min:0',
            'details.*.credit' => 'nullable|numeric|min:0',
            'details.*.description' => 'nullable|string',
            'details.*.person' => 'nullable|string',
            'enclosure' => ['nullable', 'array'],
            'enclosure.*' => [
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'max:2048', // 2 MB per file
            ],
        ];
    }

    public function messages()
    {
        return [
            'details.required' => 'Detail jurnal wajib diisi.',
            'details.*.account_id.required' => 'Akun wajib dipilih.',
        ];
    }
}
