<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role yang Termasuk Eksternal Perusahaan
    |--------------------------------------------------------------------------
    | Daftar role yang bila dimiliki oleh user, maka user tersebut otomatis
    | dianggap sebagai eksternal dan harus ada di tabel masing-masing.
    */

    'roles' => [
        'Customer',
        'Affiliator',
    ],

     'models' => [
        'Customer' => App\Models\Customer::class,
        'Affiliator' => App\Models\Affiliator::class,
    ],
];
