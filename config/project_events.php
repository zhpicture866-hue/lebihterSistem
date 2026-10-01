<?php

return [
    'project_created' => [
        'title' => 'Proyek Baru',
        'message' => [
            'created_self' => 'Selamat, Anda berhasil membuat project',
            'assigned'     => 'Anda ditugaskan ke proyek baru',
            'customer'     => 'Data proyek Anda berhasil disimpan dan sekarang sedang masuk tahap penawaran harga',
            'director'     => 'Ada proyek baru yang perlu ditinjau',
        ],
    ],

    'consult_created' => [
        'title' => 'Tahap Konsultasi',
        'message' => [
            'created_self' => 'Selamat, Anda telah melakukan tahap konsultasi proyek',
            'assigned'     => 'Anda telah melakukan tahap konsultasi proyek bersama customer',
            'customer'     => 'Form konsultasi Anda berhasil disimpan dan sekarang masuk ke tahap rencana survei',
        ],
    ],

    'survey_created' => [
        'title' => 'Survei',
        'message' => [
            'created_self' => 'Selamat, Anda telah berhasil menyimpan form survei',
            'assigned'     => 'Anda ditugaskan untuk melakukan kegiatan survei proyek',
            'customer'     => 'Form survei Anda berhasil disimpan dan sekarang masuk ke tahap penawaran',
        ],
    ],

    'planning_created_paid' => [
        'title' => 'Rencana Survei',
        'message' => [
            'created_self' => 'Rencana survei dibuat. Menunggu pembayaran.',
            'assigned'     => 'Anda akan ditugaskan setelah pembayaran dilakukan.',
            'customer'     => 'Silakan lakukan pembayaran untuk menjadwalkan survei.',
        ],
    ],

    'planning_created_free' => [
        'title' => 'Rencana Survei',
        'message' => [
            'created_self' => 'Selamat, Anda telah berhasil menyimpan form rencana survei',
            'assigned' => 'Anda direncanakan untuk melakukan survei proyek',
            'customer' => 'Form rencana survei Anda berhasil disimpan dan sekarang masuk ke tahap survei',
        ],
    ],

    'planning_paid' => [
        'title' => 'Pembayaran Survei',
        'message' => [
            'Super-Admin'    => 'Customer telah melakukan pembayaran survei.',
            'assigned' => 'Survei sudah dibayar dan siap dijalankan.',
            'customer' => 'Pembayaran survei berhasil. Tim kami akan segera datang.',
        ],
    ],

    'dp_paid' => [
        'title' => 'Pembayaran DP',
        'message' => [
            'Super-Admin'    => 'Customer telah melakukan pembayaran dp Desain.',
            'assigned' => 'Survei sudah dibayar dan siap dijalankan.',
            'customer' => 'Pembayaran dp desain berhasil.',
        ],
    ],

    'final_paid' => [
        'title' => 'Pembayaran Lunas',
        'message' => [
            'Super-Admin'    => 'Customer telah melakukan pembayaran survei.',
            'assigned' => 'Survei sudah dibayar dan siap dijalankan.',
            'customer' => 'Sisa pembayaran berhasil.',
        ],
    ],

    'rab_paid' => [
        'title' => 'Pembayaran RAB',
        'message' => [
            'Super-Admin'    => 'Customer telah melakukan pembayaran survei.',
            'assigned' => 'Survei sudah dibayar dan siap dijalankan.',
            'customer' => 'Pembayaran jasa pembuatan RAB berhasil',
        ],
    ],

'planning_rejected' => [
    'title' => 'Rencana Survei Ditolak',
    'message' => [
        'Super-Admin'    => 'Customer menolak rencana survei.',
        'assigned' => 'Rencana survei ditolak oleh customer.',
    ],
],

    'offer_created' => [
        'title' => 'Penawaran Jasa Desain',
        'message' => [
            'created_self' => 'Form penawaran jasa desain berhasil disimpan',
            'customer'     => 'Form penawarn desain berhasil disimpan dan sekarang masuk ke tahap kontrak',
        ],
    ],

    'offerrab_created' => [
        'title' => 'Penawaran Jasa RAB',
        'message' => [
            'created_self' => 'Form penawaran RAB berhasil disimpan',
            'customer'     => 'Form penawarn RAB berhasil disimpan dan sekarang masuk ke tahap invoice',
        ],
    ],

    'offerbuild_created' => [
        'title' => 'Penawaran Jasa Build',
        'message' => [
            'created_self' => 'Form penawaran Build berhasil disimpan',
            'customer'     => 'Form penawarn Build berhasil disimpan dan sekarang masuk ke tahap kontrak',
        ],
    ],

    'invoice_created' => [
        'title' => 'Invoice',
        'message' => [
            'customer' => 'Invoice baru menunggu pembayaran',
        ],
    ],

    'contract_created' => [
        'title' => 'Kontrak Desain',
        'message' => [ 
            'Super-Admin'    => 'Selamat, Anda telah berhasil menyimpan form survei',
            'customer' => 'Kontrak Desain berhasil disimpan dan sekarang masuk ke tahap Pembayaran DP Desain',
        ],
    ],
'task_assigned' => [
    'title' => 'Task Baru',
    'message' => [
        'assigned' => 'Anda ditugaskan mengerjakan task baru.',
        'admin'    => 'Task baru telah ditugaskan ke PIC.',
    ],
],

'task_file_uploaded' => [
    'title' => 'File Task Diupload',
    'message' => [
        'admin'    => 'PIC telah mengupload file task.',
        'customer'    => 'Petugas sudah mengupload file, mohon untuk diperiksa',
    ],
],

'invoice_build_approved' => [
    'title' => 'Invoice Tahap :termin Disetujui',
    'message' => [
        'Super-Admin' => 'Invoice pembayaran event tahap :termin senilai :amount telah disetujui.',
        'customer'    => 'Pembayaran tahap :termin senilai :amount berhasil dikonfirmasi. Terima kasih!',
    ],
],

'task_rejected' => [
    'title' => 'Task Direvisi',
    'message' => [
        'assigned' => 'Task Anda direvisi. Silakan perbaiki.',
        'admin'    => 'Task telah direvisi.',
        'customer' => 'Task :task perlu direvisi.',
    ],
],

'invoice_build_created' => [
    'title' => 'Invoice Event Tahap :termin Dibuat',
    'message' => [
        'Super-Admin' => 'Invoice event tahap :termin (:progress_start%–:progress_end%) senilai :amount telah dibuat.',
        'customer'    => 'Invoice pembayaran event tahap :termin senilai :amount telah tersedia. Silakan lakukan pembayaran.',
    ],
],

];