<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountingAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Worker;

class AjaxController extends Controller
{
    public function getAccounts()
    {
        $licenseId = config('app.license_id');

        $accounts = AccountingAccount::where('license_id', $licenseId)
            ->where('is_parent', false)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get([
                'id',
                'account_code',
                'account_name',
                'person_type'
            ]);

        return response()->json($accounts);
    }

public function getCustomers()
{
    $customers = Customer::with('user:id,fullname')
        ->get()
        ->map(fn ($cus) => [
            'id'   => $cus->id,
            'name' => $cus->user?->fullname ?? '-',
        ])
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();

    return response()->json($customers);
}

public function getEmployees()
{
    $employees = Employee::with('user:id,fullname')
        ->get()
        ->map(fn ($emp) => [
            'id'   => $emp->id,
            'name' => $emp->user?->fullname ?? '-',
        ])
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();

    return response()->json($employees);
}

    public function getWorkers()
    {
        $workers = Worker::with('user')
            ->get()
            ->map(function ($work) {
                return [
                    'id'   => $work->id,
                    'name' => $work->user?->fullname ?? '-',
                ];
            });

        return response()->json($workers);
    }
}