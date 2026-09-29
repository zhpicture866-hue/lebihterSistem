<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferBuildRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

public function rules()
{
    return [

        'project_id'     => 'required|uuid|exists:projects,id',
        'rab_process_id' => 'required|exists:rab_process,id',

        'offer_number' => 'nullable|string|max:255',
        'offer_date'   => 'nullable|date',
        'contact_name' => 'nullable|string|max:255',

        'volume'      => 'nullable|numeric|min:0',
        'price_meter' => 'nullable|numeric|min:0',
        'total_price' => 'nullable|numeric|min:0',

        'discount'    => 'nullable|numeric|min:0',
        'tax_rate'    => 'nullable|numeric|min:0',
        'total_tax'   => 'nullable|numeric|min:0',
        'shipping'    => 'nullable|numeric|min:0',
        'grand_total' => 'nullable|numeric|min:0',

        'notes' => 'nullable|string',
    ];
}
}
