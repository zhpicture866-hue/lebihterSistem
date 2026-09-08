<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountingAccountController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AccountingJournalController;
use App\Http\Controllers\AccountingReportController;
use App\Http\Controllers\AccountingPeriodController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\AffiliatorController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectLevelController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\ArchitectController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\Sso\SsoController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\SupplierCatalogController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\DesignPackageController;
use App\Http\Controllers\RabPackageController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\JobCategoryController;
use App\Http\Controllers\InvoiceBuildController;
use App\Http\Controllers\BuildProcessItemController;
use App\Http\Controllers\BuildDailyController;
use App\Http\Controllers\BuildWeeklyController;
use App\Http\Controllers\BuildWeeklyPlanController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\Api\JournalApiController;
use App\Http\Controllers\JournalExportController;
use App\Http\Controllers\KasController;


Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';
// Auth::routes();
Route::get('/home', function () {
    return redirect('/dashboard');
})->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/sso/authorize', [SsoController::class, 'authorize'])
        ->name('sso.authorize');
});
Route::middleware(['auth', 'role:Super-Admin'])->group(function () {
    Route::resource('/menus', MenuController::class);
});

Route::get('/customer/profile', [DashboardController::class, 'edit'])->name('customer.profile');
Route::put('/customer/profile', [DashboardController::class, 'update'])->name('customer.update');
Route::get('/affiliators/profile', [DashboardController::class, 'edit'])->name('affiliators.profile');

Route::middleware(['auth', 'permission:lihat daftar karyawan|lihat data karyawan'])->group(function () {
    Route::resource('/employees', EmployeeController::class)->whereUuid('employee');
});

Route::get('/employees/generate-nik', [EmployeeController::class, 'generateNikAjax'])
    ->name('employees.generateNik');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'permission:lihat daftar customer|lihat data customer', 'activerole:Customer'])
    ->group(function () {
        Route::get('/customers/generate-nic', [CustomersController::class, 'generateNicAjax'])->name('customers.generateNic');
        Route::resource('/customers', CustomersController::class)->whereUuid('customer');
    });

Route::middleware(['auth', 'permission:lihat daftar affiliator|lihat data affiliator', 'activerole:Affiliator'])
    ->group(function () {
        Route::get('/affiliators/generate-nia', [AffiliatorController::class, 'generateNiaAjax'])->name('affiliators.generateNia');
        Route::resource('/affiliators', AffiliatorController::class)->whereUuid('affiliator');
    });

Route::middleware(['auth', 'permission:lihat daftar supplier|lihat data supplier','activerole:Mitra Supplier,Direktur' ])->group(function () {
    Route::get('/suppliers/generate-SupplierId', [SupplierController::class, 'generateSupplierIdAjax'])->name('suppliers.generateSupplierId');
    Route::get('/supplier/{supplier}/products-datatable', [SupplierController::class, 'datatableProducts'])
    ->name('supplier.products.datatable');

    Route::resource('/suppliers', SupplierController::class);
    Route::post('/suppliers/{supplier}/duplicate-product/{product}', 
    [SupplierController::class, 'duplicateProduct']
)->name('suppliers.duplicateProduct');
});

// Supplier Catalog
Route::get('/supplier/search-product', [SupplierCatalogController::class, 'searchProduct'])
    ->name('supplier.searchProduct');

Route::get('/supplier/product-detail/{id}', [SupplierCatalogController::class, 'productDetail'])
    ->name('supplier.productDetail');

// Create product baru via AJAX (tidak pakai reload)
Route::post('/products/store-ajax', [ProductController::class, 'storeAjax'])
    ->name('products.store.ajax');

// Simpan produk ke supplier (pivot)
Route::post('/supplier/products/store', [SupplierCatalogController::class, 'storeSupplierProduct'])
    ->name('supplier.products.store');

Route::put('/supplier-product/update-price',
    [SupplierCatalogController::class, 'updatePrice']
)->name('supplier-product.update-price');

Route::delete('/supplier-product/{pivot}', [SupplierCatalogController::class, 'destroy'])
    ->name('supplier-product.destroy');
Route::post('/supplier-product/restore/{id}', [SupplierCatalogController::class, 'restore']);

Route::put('/supplier-product/update-label', [SupplierCatalogController::class, 'updateLabel'])
    ->name('supplier-product.update-label');

Route::get('/catalog/supplier', [ProductCatalogController::class, 'supplierCatalog'])
    ->name('catalog.supplier');

Route::get('/catalog/customer', [ProductCatalogController::class, 'customerCatalog'])
    ->name('catalog.customer');




 Route::middleware(['auth', 'permission:lihat daftar investor|lihat data investor', 'activerole:Investor'])->group(function () {
       Route::get('/investors/generate-InvestorId', [InvestorController::class, 'generateInvestorIdAjax'])->name('investors.generateInvestorId');
     Route::resource('/investors', InvestorController::class);
});

Route::middleware(['auth', 'permission:lihat daftar arsitek|lihat data arsitek', 'activerole:Mitra Arsitek'])->group(function () {
   Route::get('/architects/generate-ArchitectId', [ArchitectController::class, 'generateArchitectIdAjax'])->name('architects.generateArchitectId');
   Route::resource('/architects', ArchitectController::class);
});

Route::middleware(['auth', 'permission:lihat daftar tukang|lihat data tukang', 'activerole:Tukang'])->group(function () {
   Route::get('/workers/generate-WorkerId', [WorkerController::class, 'generateWorkerIdAjax'])->name('workers.generateWorkerId'); 
   Route::resource('/workers', WorkerController::class);
});

Route::middleware(['auth', 'permission:lihat daftar kontraktor|lihat data kontraktor', 'activerole:Mitra Kontraktor'])->group(function () {
    Route::get('/contractors/generate-SupplierId', [ContractorController::class, 'generateContractorIdAjax'])->name('contractors.generateContractorId');
    Route::resource('/contractors', ContractorController::class);
});

Route::get('/accounting/generate-code', [AccountingAccountController::class, 'generateCode']);

Route::middleware(['auth', 'permission:lihat akun-akuntansi'])
        ->resource('accounting', AccountingAccountController::class)
         ->parameters(['accounting' => 'account']);


Route::get('/journals/{journal}/print', [AccountingJournalController::class, 'print'])
    ->name('journals.print');


Route::get('/journals/report', [AccountingJournalController::class, 'report'])
    ->name('journals.report')
    ->middleware(['auth', 'permission:lihat transaksi']);

Route::get('/journals/general', [AccountingJournalController::class, 'generalJournal'])
    ->name('journals.general')
    ->middleware(['auth', 'permission:lihat jurnal umum']);
Route::get('/journals/export/pdf', [AccountingJournalController::class, 'exportPDF'])->name('journals.export.pdf');

Route::get('/journals/ledger', [AccountingJournalController::class, 'ledger'])
    ->name('journals.ledger')
    ->middleware(['auth', 'permission:lihat buku besar']);
Route::get('/journals/ledgerpdf', [AccountingJournalController::class, 'exportLedgerPdf'])->name('ledgerpdf');

Route::get('/journals/export/trial-pdf', [AccountingJournalController::class, 'exportTrial'])
    ->name('journals.trial.pdf');

Route::get('/journals/income/pdf', [AccountingJournalController::class, 'exportIncome'])
    ->name('journals.income.pdf');
Route::get('/check-period', [AccountingJournalController::class, 'checkPeriod']);

Route::middleware(['auth', 'permission:lihat jurnal'])->group(function () {
    Route::resource('/journals', AccountingJournalController::class);
});

Route::get('/reports/balance_sheet', [AccountingJournalController::class, 'balanceSheet'])
    ->name('reports.balance_sheet')
    ->middleware(['auth', 'permission:lihat neraca']);

Route::get('/reports/income-statement', [AccountingReportController::class, 'incomeStatement'])
    ->name('reports.income_statement')
    ->middleware(['auth', 'permission:lihat laba rugi']);
Route::get('/reports/income-statement/pdf', [AccountingReportController::class, 'exportPdf'])
    ->name('reports.income_statement.pdf');

Route::get('/reports/income-statement/excel', [AccountingReportController::class, 'exportExcel'])
    ->name('reports.income_statement.excel');

Route::get('/journals/{journal}/export', [JournalExportController::class, 'export'])
    ->name('journals.export');

Route::get('/journals/export/general', [JournalExportController::class, 'exportGeneral'])
    ->name('general.export');

Route::get('/ledger/export', [JournalExportController::class, 'exportLedger'])
    ->name('ledger.export');

Route::get('/trial/export', [JournalExportController::class, 'exportTrialBalance'])
    ->name('trial.export');


    Route::get('/periods', [AccountingPeriodController::class, 'index'])->name('periods.index')->middleware(['auth', 'permission:lihat tutup buku']);
    Route::post('/periods/close', [AccountingPeriodController::class, 'close'])->name('periods.close');
    Route::post('/periods/reopen', [AccountingPeriodController::class, 'reopen'])->name('periods.reopen');
    Route::delete('/periods/{period}', [AccountingPeriodController::class, 'destroy'])
    ->name('periods.destroy');

Route::post('/switch-role', [RoleSwitchController::class, 'switch'])
    ->middleware('auth')
    ->name('switch.role');

route::resource('/product_colors', ProductColorController::class);
route::resource('/product_brands', ProductBrandController::class);
route::resource('/product_categories', ProductCategoryController::class);
route::resource('/product_types', ProductTypeController::class);



Route::middleware(['auth', 'permission:lihat daftar produk|lihat data produk'])->group(function () {
    Route::resource('/products', ProductController::class);
});

Route::middleware(['auth', 'permission:lihat daftar produk'])->group(function () {
    Route::resource('/products/catalog', ProductCatalogController::class);
});

Route::post('/products/generate-sku', [ProductController::class, 'generateSku'])
    ->name('products.generateSku');


Route::middleware(['auth', 'permission:lihat daftar gudang|lihat data gudang'])->group(function () {
    route::resource('/warehouses', WarehouseController::class);
});


Route::get('/warehouse/search-product', [SupplierCatalogController::class, 'searchProduct'])
    ->name('warehouse.searchProduct');

Route::post('/warehouse/products/store', [SupplierCatalogController::class, 'storeSupplierProduct'])
    ->name('warehouse.products.store');

Route::middleware(['auth', 'permission:lihat daftar proyek|lihat data proyek'])->group(function () {

    Route::get('/projects/{project}/continue', 
    [ProjectController::class, 'continue'])
    ->name('projects.continue');

    Route::resource('/projects', ProjectController::class)->except(['edit, update, show']);
    Route::get('prjects/{project}/pdf', [ProjectController::class, 'pdf'])
    ->name('projects.pdf');
});

Route::middleware(['auth', 'permission:lihat data tenaga'])->group(function () {
    Route::post('/labor_costs/{id}/duplicate', [\App\Http\Controllers\LaborCostController::class, 'duplicate'])
        ->name('labor_costs.duplicate');
    Route::resource('/labor_costs', \App\Http\Controllers\LaborCostController::class);
});

Route::middleware(['auth', 'permission:lihat data alat'])->group(function () {
    Route::post('/tools/{id}/duplicate', [\App\Http\Controllers\EquipmentCostController::class, 'duplicate'])
        ->name('tools.duplicate');
    Route::resource('/equipment_costs', \App\Http\Controllers\EquipmentCostController::class);
});

Route::resource('design-packages', DesignPackageController::class)
    ->except(['show']);

// Tambah / update / hapus item
Route::post('design-packages/{designPackage}/items',
    [DesignPackageController::class, 'addItem'])->name('design-packages.items.store');

Route::put('design-package-items/{item}',
    [DesignPackageController::class, 'updateItem'])->name('design-packages.items.update');

Route::delete('design-package-items/{item}',
    [DesignPackageController::class, 'deleteItem'])->name('design-packages.items.delete');

// API aman untuk frontend
Route::get('design-packages/json/{id}',
    [DesignPackageController::class, 'getPackage'])->name('design-packages.json');

Route::resource('rab-packages', RabPackageController::class)
    ->except(['show']);

// Tambah / update / hapus item
Route::post('rab-packages/{rabPackage}/items',
    [RabPackageController::class, 'addItem'])->name('rab-packages.items.store');

Route::put('rab-package-items/{item}',
    [RabPackageController::class, 'updateItem'])->name('rab-packages.items.update');

Route::delete('rab-package-items/{item}',
    [RabPackageController::class, 'deleteItem'])->name('rab-packages.items.delete');

// API aman untuk frontend
Route::get('rab-packages/json/{id}',
    [RabPackageController::class, 'getPackage'])->name('rab-packages.json');
// Route::get('/job-categories/import-upah', function () {
//     return view('job-categories.import_upah');
// })->middleware('auth');

// Route::post('/job-categories/import-upah', [\App\Http\Controllers\UpahImportController::class, 'importUpah'])
//     ->middleware('auth')
//     ->name('job-categories.import-upah');
Route::post('/job-categories/{id}/duplicate', [JobCategoryController::class, 'duplicate'])
    ->name('job-categories.duplicate');
Route::resource('/job-categories', JobCategoryController::class)
    ->except(['show']);
Route::post('job-categories/{jobCategory}/items',
    [JobCategoryController::class, 'addItem'])->name('job-categories.items.store');

Route::put('job-categories-items/{item}',
    [JobCategoryController::class, 'updateItem'])->name('job-categories.items.update');

Route::delete('job-categories-items/{item}',
    [JobCategoryController::class, 'deleteItem'])->name('job-categories.items.delete');
    
Route::post(
    'job-categories/{jobCategory}/overhead-profit',
    [JobCategoryController::class, 'saveOverheadProfit']
)->name('job-categories.save-overhead-profit');

Route::get('/ajax/items/{type}', [JobCategoryController::class, 'getItems']);
Route::get('/ajax/item-detail/{type}/{id}', [JobCategoryController::class, 'getItemDetail']);
Route::get('/ajax/product/{id}/suppliers', [JobCategoryController::class, 'getSuppliersByProduct']);
Route::get('/ajax/product-supplier/{supplierId}', [JobCategoryController::class, 'getProductSupplierById']);

Route::get('/job-categories/{id}/simple', [JobCategoryController::class, 'simple']);
Route::post('/job-categories/update-effective/{id}', 
    [JobCategoryController::class, 'updateEffective']
)->name('job-categories.update-effective');

Route::post('/job-items/{item}/change-supplier', [\App\Http\Controllers\JobCategoryItemController::class, 'changeSupplier']);
Route::post('/job-items/{item}/change-uraian', 
    [\App\Http\Controllers\JobCategoryItemController::class, 'changeUraian']
)->name('job-items.change-uraian');
Route::post('/notifications/{id}/read', function ($id) {
    auth()->user()->notifications()
        ->where('id', $id)
        ->update(['read_at' => now()]);

    return response()->json(['success' => true]);
})->middleware('auth');
// Route::post('/notifications/read-all', function () {
//     auth()->user()->unreadNotifications->markAsRead();
//     return response()->json(['status' => 'ok']);
// })->name('notifications.readAll');
Route::post('/notifications/read-all', function () {
    auth()->user()->unreadNotifications->markAsRead();

    return response()->json(['status' => 'ok']);
});


Route::middleware(['auth'])->group(function () {
    Route::get('consultations/{consultation}/pdf', [\App\Http\Controllers\ConsultationController::class, 'pdf'])
    ->name('consultations.pdf');
    Route::get('plannings/{planning}/pdf', [\App\Http\Controllers\PlanningController::class, 'pdf'])
    ->name('plannings.pdf');
    Route::get('surveys/{survey}/pdf', [\App\Http\Controllers\SurveyController::class, 'pdf'])
    ->name('surveys.pdf');
    Route::get('/projects/offers/{offer}/pdf', [\App\Http\Controllers\OfferController::class, 'printPdf'])
    ->name('projects.offers.desain.pdf');
    Route::get('/projects/offer/{offer}/pdf', [\App\Http\Controllers\OfferRABController::class, 'printPdf'])
    ->name('projects.offers.rab.pdf');
    Route::get('/projects/{project}/build/pdf', [\App\Http\Controllers\OfferBuildController::class, 'printPdf'])
    ->name('projects.offers.build.pdf');
    Route::get('/projects/{project}/rab/pdf', [\App\Http\Controllers\RabProcessController::class, 'exportPdf'])
    ->name('projects.rab.pdf');
    Route::get('/tasks/files/{file}', [\App\Http\Controllers\ProjectTaskController::class, 'viewFile'])
    ->name('tasks.files.view');
    Route::post('/tasks/{task}/approve', [\App\Http\Controllers\ProjectTaskController::class, 'approve'])
    ->name('tasks.approve');
    Route::post('/tasks/{task}/reject', [\App\Http\Controllers\ProjectTaskController::class, 'reject'])
    ->name('tasks.reject');
    Route::get(
    '/projects/{project}/invoice-final',
    [\App\Http\Controllers\InvoiceController::class, 'invoiceFinal']
)->name('projects.invoice.final');
Route::get(
    'projects/{project}/contract/pdf',
    [\App\Http\Controllers\ContractController::class, 'pdf']
)->name('projects.contract.pdf');

Route::get(
    'projects/{project}/contract/buildpdf',
    [\App\Http\Controllers\ContractBuildController::class, 'buildpdf']
)->name('projects.contract.buildpdf');

Route::get(
    'projects/{project}/invoice/pdf',
    [\App\Http\Controllers\InvoiceController::class, 'invoiceDp']
)->name('projects.invoice.pdf');

Route::get(
    'projects/{project}/invoice/invoice-rab',
    [\App\Http\Controllers\InvoiceController::class, 'invoiceRab']
)->name('projects.invoice.rab');
Route::post(
    '/projects/{project}/contract/approve',
    [\App\Http\Controllers\ContractController::class, 'approve']
)->name('projects.contract.approve');
Route::post(
    '/projects/{project}/contract/approvebuild',
    [\App\Http\Controllers\ContractBuildController::class, 'approve']
)->name('projects.contract.build.approve');

Route::post(
    '/projects/{project}/invoice/approve',
    [\App\Http\Controllers\InvoiceController::class, 'approve']
)->name('projects.invoice.approve');

Route::post(
    '/projects/{project}/invoice-rab/approve',
    [\App\Http\Controllers\InvoiceController::class, 'approveRab']
)->name('projects.invoice.rab.approve');

    Route::get(
        '/projects/{project}/planning-survey/pdf',
        [\App\Http\Controllers\InvoiceController::class, 'surveyPlanningPdf']
    )->name('projects.planning-survey.pdf');

Route::get(
    '/survey-invoice/{invoice}/{token}/approve',
    [\App\Http\Controllers\InvoiceController::class, 'approveSurvey']
)->name('survey.invoice.approve');

Route::get(
    '/survey-invoice/{invoice}/{token}/reject',
    [\App\Http\Controllers\InvoiceController::class, 'rejectSurveyForm']
)->name('survey.invoice.reject.form');

Route::post(
    '/survey-invoice/{invoice}/{token}/reject',
    [\App\Http\Controllers\InvoiceController::class, 'rejectSurvey']
)->name('survey.invoice.reject');

Route::get(
    '/projects/{project}/invoice-survey',
    [\App\Http\Controllers\InvoiceController::class, 'invoiceSurvey']
)->name('projects.invoice-survey');
Route::post(
    '/projects/{project}/invoice-final/approve',
    [\App\Http\Controllers\InvoiceController::class, 'approveFinal']
)->name('projects.invoice.final.approve');

Route::middleware(['permission:lihat daftar proyek'])->group(function () {

Route::post('projects/consultations', [\App\Http\Controllers\ConsultationController::class, 'store'])
    ->name('projects.consultations.store');

Route::put('consultations/{consultation}', [\App\Http\Controllers\ConsultationController::class, 'update'])
    ->name('consultations.update');

Route::post('projects/plannings', [\App\Http\Controllers\PlanningController::class, 'store'])
    ->name('projects.plannings.store');

Route::put('plannings/{planning}', [\App\Http\Controllers\PlanningController::class, 'update'])
    ->name('plannings.update');

Route::post('projects/surveys', [\App\Http\Controllers\SurveyController::class, 'store'])
    ->name('projects.surveys.store');

Route::put('surveys/{survey}', [\App\Http\Controllers\SurveyController::class, 'update'])
    ->name('surveys.update');

Route::delete('/survey-images/{id}', [\App\Http\Controllers\SurveyImageController::class, 'destroy']);
Route::delete('/survey-documents/{id}', [\App\Http\Controllers\SurveyDocumentController::class, 'destroy']);
Route::delete('/survey-documentations/{id}', [\App\Http\Controllers\SurveyDocumentationController::class, 'destroy']);

Route::post('projects/offers', [\App\Http\Controllers\OfferController::class, 'store'])
    ->name('projects.offers.store');
    
Route::put('offers/{offer}', [\App\Http\Controllers\OfferController::class, 'update'])
    ->name('offers.update');

Route::post('/offers/{offer}/approve', [\App\Http\Controllers\OfferController::class, 'approve'])
    ->name('offers.approve');

Route::post('/offers/{offer}/reject', [\App\Http\Controllers\OfferController::class, 'reject'])
    ->name('offers.reject');

Route::post('projects/offer', [\App\Http\Controllers\OfferRABController::class, 'store'])
    ->name('projects.offer.store');
    
// Route::put('offer/{offer}', [\App\Http\Controllers\OfferRABController::class, 'update'])
//     ->name('offer.update');

Route::put('offer-rab/{offer}', [\App\Http\Controllers\OfferRABController::class, 'update']) 
    ->name('offer-rab.update');

Route::post('/offer/{offer}/approve', [\App\Http\Controllers\OfferRABController::class, 'approve'])
    ->name('offer.approve');

Route::post('/offer/{offer}/reject', [\App\Http\Controllers\OfferRABController::class, 'reject'])
    ->name('offer.reject');

Route::post('projects/rab', [\App\Http\Controllers\RabProcessController::class, 'store'])
    ->name('projects.rab.store');
Route::put('/projects/{project}/rab/{rab}', [\App\Http\Controllers\RabProcessController::class, 'update'])
    ->name('projects.rab.update');

Route::post('/projects/rab/{rab}/refresh-from-master', [\App\Http\Controllers\RabProcessController::class, 'refreshFromMaster'])
    ->name('rab.refreshFromMaster');

Route::get('/rab-process/{id}/items',
    [\App\Http\Controllers\RabProcessController::class, 'items'])
    ->name('rab.process.items.json');
Route::post('/rab-images/upload', [\App\Http\Controllers\RabProcessController::class,'upload']);
Route::delete('/rab-images/{id}', [\App\Http\Controllers\RabProcessController::class,'destroy']);
Route::get(
    '/rab/uraian-images/{uraianId}',
    [\App\Http\Controllers\RabProcessController::class, 'uraianImages']
)->name('rab.uraian-images');
Route::get('/rab/{id}/structure', [\App\Http\Controllers\RabProcessController::class,'structure']);
// Route::post('/rab/autosave/{rab}', [\App\Http\Controllers\RabProcessController::class, 'autosave']);
// Route::post('/rab/reorder/{rab}', [\App\Http\Controllers\RabProcessController::class, 'reorder']);
// Route::get('/rab/autosave/{rab}', [\App\Http\Controllers\RabProcessController::class, 'loadDraft']);
Route::post('projects/offerbuild', [\App\Http\Controllers\OfferBuildController::class, 'store'])
    ->name('projects.offerbuild.store');
    
// Route::put('offer/{offer}', [\App\Http\Controllers\OfferBuildController::class, 'update'])
//     ->name('offer.update');

Route::put('offer-build/{offer}', [\App\Http\Controllers\OfferBuildController::class, 'update']) 
    ->name('offer-build.update');

Route::post('/offer/{offer}/approve', [\App\Http\Controllers\OfferBuildController::class, 'approve'])
    ->name('offer.approve');

Route::post('/offer/{offer}/reject', [\App\Http\Controllers\OfferBuildController::class, 'reject'])
    ->name('offer.reject');

Route::post('/tasks/{task}/assign', [\App\Http\Controllers\ProjectTaskController::class, 'assign'])
    ->name('tasks.assign');

Route::post('/tasks/{task}/upload', [\App\Http\Controllers\ProjectTaskController::class, 'uploadFile'])
    ->name('tasks.upload');

Route::post('/tasks/{task}/complete', [\App\Http\Controllers\ProjectTaskController::class, 'complete'])
    ->name('tasks.complete');

Route::delete(
    '/tasks/files/{file}',
    [\App\Http\Controllers\ProjectTaskController::class, 'deleteFile']
)->name('tasks.files.delete');
Route::post('/projects/{project}/sync-tasks',
    [\App\Http\Controllers\ProjectTaskController::class, 'syncFromOffer']
)->name('projects.tasks.sync');
// Route::get('/survey-invoice/{invoice}/approve', [\App\Http\Controllers\SurveyInvoiceController::class, 'approve'])
//     ->name('survey.approve');

// Route::post('/survey-invoice/{invoice}/reject', [\App\Http\Controllers\SurveyInvoiceController::class, 'reject'])
//     ->name('survey.reject.form');

Route::post(
    '/projects/{project}/final',
    [\App\Http\Controllers\FinalProjectController::class, 'store']
)->name('projects.finals.store');
Route::delete(
    '/projects/{project}/final',
    [\App\Http\Controllers\FinalProjectController::class, 'destroy']
)->name('projects.finals.destroy');
Route::post(
    '/projects/{project}/finalBuild',
    [\App\Http\Controllers\FinalBuildProjectController::class, 'store']
)->name('projects.finals-build.store');
Route::delete(
    '/projects/{project}/finalBuild',
    [\App\Http\Controllers\FinalBuildProjectController::class, 'destroy']
)->name('projects.finals-build.destroy');
});

Route::middleware(['auth', 'permission:lihat daftar user'])->group(function () {
    route::resource('/users', UsersController::class);
});
});

Route::prefix('projects/{project}')
    ->middleware(['auth'])
    ->group(function () {

        // Download invoice build per termin
        Route::get(
            '/invoice/build/termin/{termin}',
            [InvoiceBuildController::class, 'invoiceBuild']
        )->name('projects.invoice.build');

        Route::get(
'/invoice-build-justek',
[InvoiceBuildController::class,'invoiceJustek']
)->name('projects.invoice.build.justek');
        // Approve invoice build
        Route::post('/invoice-build-justek-auto',
    [InvoiceBuildController::class,'autoJustek']
)->name('projects.invoice.justek.auto');
        Route::post(
            '/invoice/build/{invoice}/approve',
            [InvoiceBuildController::class, 'approve']
        )->name('projects.invoice.build.approve');
    });

Route::middleware(['auth'])
    ->post('/build-items/update-bobot',
        [BuildProcessItemController::class, 'updateBobot']
    )->name('build-items.update-bobot');
Route::post('/build-items/tambahan', 
    [BuildProcessItemController::class, 'storeTambahan']
)->name('build-items.store-tambahan');
Route::post(
    '/projects/{project}/weekly-report',
    [BuildWeeklyController::class, 'store']
)->name('weekly-report.store');

Route::get('/projects/{project}/invoice-panel',
[ProjectController::class,'invoicePanel'])
->name('projects.invoice.panel');
Route::get(
    '/projects/items/{item}/tambahan',
    [ProjectController::class, 'loadTambahan']
);
Route::post(
    '/projects/{project}/sync-build',
    [ProjectController::class, 'syncBuildProcess']
)->name('projects.sync-build');
Route::post(
    '/projects/{project}/sync-build-plan',
    [ProjectController::class,'syncBuildPlan']
)->name('projects.sync-build-plan');
Route::post(
    '/project/{project}/build-plan/data',
    [ProjectController::class,'data']
)
->name('build-plan.data');

Route::get(
    '/projects/{project}/build-process-partial',
    [ProjectController::class, 'buildProcessPartial']
)->name('projects.build-process.partial');
Route::get('/projects/{project}/export-pdf', 
    [BuildWeeklyController::class, 'exportPdf'])
    ->name('projects.export-pdf');
Route::middleware(['auth', 'permission:lihat daftar role'])->group(function () {
    Route::resource('/roles', RoleController::class);
    Route::post('/roles/{role}/update-permissions', [RoleController::class, 'updatePermissions'])->name('roles.updatePermissions');
});
Route::middleware(['auth', 'role:Super-Admin'])->group(function () {
    Route::resource('/permissions', PermissionController::class);
});
Route::middleware(['auth', 'permission:kelola akun'])->group(function () {
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts/update-role', [AccountController::class, 'updateRole'])->name('accounts.update-role');
});

Route::middleware(['auth', 'permission:lihat daftar dokumen'])->group(function () {
    route::resource('/documents', DocumentController::class);
});

Route::middleware(['auth'])->group(function () {

    // Route::resource('attendances', AttendanceController::class)
    //     ->middleware('permission:lihat daftar absensi');
    Route::get('/attendances', [AttendanceController::class, 'index'])
    ->name('attendances.index');

    Route::get('/attendances/datatable', [AttendanceController::class, 'datatable'])
        ->name('attendances.datatable');
    Route::post('attendances/check-in', [AttendanceController::class, 'checkIn'])
        // ->middleware('permission:tambah data absensi')
        ->name('attendances.check-in');

    Route::post('attendances/check-out', [AttendanceController::class, 'checkOut'])
        // ->middleware('permission:tambah data absensi')
        ->name('attendances.check-out');
});
Route::middleware(['auth'])->group(function () {
    // sinkronisasi lisensi aktif dari navbar (POST dari form/navbar)
    // Route::post('/active-license', [LicenseSessionController::class, 'set'])->name('active-license.set');

    // data untuk form jurnal
    Route::get('/get-accounts', [AjaxController::class, 'getAccounts']);
    Route::get('/get-employees', [AjaxController::class, 'getEmployees']);
    Route::get('/get-customers', [AjaxController::class, 'getCustomers']);
    Route::get('/get-workers', [AjaxController::class, 'getWorkers']);

    Route::get('/ajax/journals/next-code', [AccountingJournalController::class, 'getNextCode']);
});

Route::get('/kas/export/excel', [KasController::class, 'exportExcel'])->name('kas.export.excel');
Route::get('/api/cities/{province_id}', function ($province_id) {
    return \App\Models\City::where('province_id', $province_id)->select('id', 'name')->get();
});

Route::get('/api/districts/{city_id}', function ($city_id) {
    return \App\Models\District::where('city_id', $city_id)->select('id', 'name')->get();
});

Route::get('/api/sub_districts/{district_id}', function ($district_id) {
    return \App\Models\SubDistrict::where('district_id', $district_id)->select('id', 'name')->get();
});

Route::get('/api/postal_codes/{sub_district_id}', function ($sub_district_id) {
    return \App\Models\PostalCode::where('sub_district_id', $sub_district_id)->select('id', 'postal_code')->get();
});

Route::get('/api/banks', function () {
    return \App\Models\Bank::select('id', 'name', 'code')->orderBy('name')->get();
});
