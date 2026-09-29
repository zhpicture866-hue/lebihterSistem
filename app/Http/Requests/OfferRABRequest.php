<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferRABRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_id'         => 'required|uuid',
            'rab_package_id'  => 'nullable|uuid',
            'offer_number'       => 'nullable|string|max:255',
            'offer_date'         => 'nullable|date',
            'contact_name'       => 'nullable|string|max:255',
            'volume'             => 'nullable|numeric|min:1',
            'satuan'             => 'nullable|string|max:50',
            'price_meter'        => 'nullable|numeric',
            'total_price'        => 'nullable|numeric',
            'discount'           => 'nullable|numeric',
            'tax_rate'           => 'nullable|numeric',
            'shipping'           => 'nullable|numeric',
            'notes'              => 'nullable|string',
            'items'                       => 'nullable|array',
            'items.*.item_name'           => 'required|string|max:255',
            'items.*.category'            => 'nullable|string|max:255',
        ];
    }
}
