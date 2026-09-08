<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountingAccount;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AccountingAccountController extends Controller
{

public function index(Request $request)
{
    $user = Auth::user();

    $query = AccountingAccount::with(['parent'])
        ->orderBy('account_code', 'asc');

    if ($user->can('lihat akun-akuntansi')) {

    } elseif ($user->can('lihat akun-akuntansi lisensi')) {

        $activeLicenseId = session('active_license_id');

        if (!$activeLicenseId) {
            abort(403, 'Silakan pilih lisensi aktif.');
        }

        $query->where('license_id', $activeLicenseId);

    } else {

        abort(403, 'Tidak memiliki akses.');

    }

    if ($request->ajax()) {

        return DataTables::of($query)

            ->addIndexColumn()

            ->addColumn('parent_name', function ($row) {
                return optional($row->parent)->account_name;
            })

            ->addColumn('is_parent', function ($row) {
                return $row->is_parent ? 'Ya' : 'Tidak';
            })

            ->addColumn('status', function ($row) {
                return $row->is_active ? 'Aktif' : 'Nonaktif';
            })
            ->editColumn('account_name', function ($row) {
                $url = route('accounting.edit', $row->id);

                $full = Str::title($row->account_name ?? '-');

                return '<a href="'.$url.'" title="'.e($full).'">'
                        .e(Str::limit($full, 35)).
                    '</a>';
            })

            ->addColumn('aksi', function ($row) {
                $buttons = '';

                if (auth()->user()->can('ubah akun-akuntansi')) {
                    $buttons .= '<a href="' . route('accounting.edit', $row->id) . '" 
                                class="btn btn-icon btn-sm btn-dark me-1">
                                <i class="ti ti-edit"></i></a>';
                }
                //  if (auth()->user()->can('lihat data proyek')) {
                //             $buttons .= '<a href="' . route('projects.show', $row->id) . '" class="btn btn-icon btn-sm btn-dark me-1" title="Lihat">
                //                             <i class="ti ti-eye"></i>
                //                         </a>';

                // }
                if (auth()->user()->can('hapus akun-akuntansi')) {
                    $buttons .= '<button data-id="' . $row->id . '" 
                                class="btn btn-icon btn-sm btn-dark delete-accounts">
                                <i class="ti ti-trash"></i></button>';
                }

                return $buttons;
            })

            ->rawColumns(['aksi', 'account_name'])
            ->make(true);
    }

    return view('accounting.index');
}

public function create(AccountingAccount $account)
{
    $user = Auth::user();

    if ($user->can('tambah akun-akuntansi')) {

        // Boleh melihat semua parent account
        $parentAccounts = AccountingAccount::where('is_parent', true)
            ->orderBy('account_code')
            ->get();

    } elseif ($user->can('tambah akun-akuntansi lisensi')) {

        $activeLicenseId = session('active_license_id');

        if (!$activeLicenseId) {
            abort(403, 'Silakan pilih lisensi aktif.');
        }

        // Hanya parent account milik lisensi aktif
        $parentAccounts = AccountingAccount::where('is_parent', true)
            ->where('license_id', $activeLicenseId)
            ->orderBy('account_code')
            ->get();

    } else {

        abort(403, 'Tidak memiliki akses.');

    }

    $categories = config('accounting.categories');
    $subCategories = config('accounting.sub_categories');

    return view('accounting.create', compact(
        'account',
        'parentAccounts',
        'categories',
        'subCategories'
    ));
}

public function store(Request $request)
{
    $request->validate([
        'account_name' => 'required|string|max:255',
        'category' => 'required|string|max:255',
        'sub_category' => 'required|string|max:255',
        'initial_balance' => 'nullable|numeric',
        'is_parent' => 'required|boolean',
        'parent_id' => ['nullable','uuid', Rule::exists(AccountingAccount::class, 'id')->where('license_id', $licenseId),],
        'person_type' => 'nullable|string',
    ]);

    $isParent = $request->boolean('is_parent');
    $parentId = $request->parent_id;

    if (!$parentId) {

        $prefix = $this->getCategoryPrefix(
            $request->category
        );

        $rootCode = "{$prefix}-000-000";

        if (
            AccountingAccount::where(
                'account_code',
                $rootCode
            )->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'parent_id' =>
                        'Akun baru wajib memiliki akun induk.'
                ]);
        }
    }

    $code = $this->generateAccountCode(
        $request->category,
        $parentId,
        $isParent
    );

    if (!$code) {

        return back()
            ->withInput()
            ->withErrors([
                'account_code' =>
                    'Kode akun tidak dapat dibuat.'
            ]);
    }

    if (
        AccountingAccount::where(
            'account_code',
            $code
        )->exists()
    ) {

        return back()
            ->withInput()
            ->withErrors([
                'account_code' =>
                    'Kode akun sudah digunakan, silakan ulangi.'
            ]);
    }

    AccountingAccount::create([
        'id' => Str::uuid(),
        'license_id' => config('app.license_id'),
        'account_code' => $code,
        'account_name' => $request->account_name,
        'category' => $request->category,
        'sub_category' => $request->sub_category,
        'initial_balance' =>
            $isParent
                ? null
                : $request->initial_balance,

        'is_parent' => $isParent,
        'parent_id' => $parentId,
        'person_type' => $request->person_type,
        'is_active' => true,
    ]);


    return redirect()
        ->route('accounting.index')
        ->with(
            'success',
            'Akun berhasil ditambahkan.'
        );
}

    public function edit(AccountingAccount $account)
    {
        $user = Auth::user();

    if ($user->can('ubah akun-akuntansi')) {

        // Boleh melihat semua parent account
        $parentAccounts = AccountingAccount::where('is_parent', true)
            ->orderBy('account_code')
            ->get();

    } elseif ($user->can('ubah akun-akuntansi lisensi')) {

        $activeLicenseId = session('active_license_id');

        if (!$activeLicenseId) {
            abort(403, 'Silakan pilih lisensi aktif.');
        }

        // Hanya parent account milik lisensi aktif
        $parentAccounts = AccountingAccount::where('is_parent', true)
            ->where('license_id', $activeLicenseId)
            ->orderBy('account_code')
            ->get();

    } else {

        abort(403, 'Tidak memiliki akses.');

    }

    $categories = config('accounting.categories');
    $subCategories = config('accounting.sub_categories');
    $parentAccounts = AccountingAccount::where('is_parent', true)->get();

    return view('accounting.edit', compact('account', 'parentAccounts', 'categories', 'subCategories'));

    }

public function update(Request $request, AccountingAccount $account)
{
    $licenseId = config('app.license_id');

    $request->validate([
        'account_code' => [
            'required',
            'string',
            'max:255',
            Rule::unique(AccountingAccount::class, 'account_code')
                ->ignore($account->id),
        ],

        'account_name' => 'required|string|max:255',

        'category' => 'required|string|max:255',

        'sub_category' => 'required|string|max:255',

        'initial_balance' => 'nullable|numeric',

        'is_parent' => 'required|boolean',

        'parent_id' => [
            'nullable',
            'uuid',
            Rule::exists(AccountingAccount::class, 'id')
                ->where('license_id', $licenseId),
        ],

        'person_type' => 'nullable|string',
    ]);


    $isParent = $request->boolean('is_parent');
    $parentId = $request->parent_id;

    if ($parentId == $account->id) {

        return back()
            ->withInput()
            ->withErrors([
                'parent_id' =>
                    'Tidak boleh memilih diri sendiri sebagai parent.'
            ]);
    }

    $parent = null;

    if ($parentId) {

        $parent = AccountingAccount::where(
            'license_id',
            $licenseId
        )
        ->find($parentId);

        if (!$parent) {

            return back()
                ->withInput()
                ->withErrors([
                    'parent_id' =>
                        'Akun induk tidak ditemukan.'
                ]);
        }

        if (
            strtoupper($parent->category)
            !== strtoupper($request->category)
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'parent_id' =>
                        'Kategori parent harus sama dengan akun.'
                ]);
        }

        $current = $parent;

        while ($current) {

            if ($current->id == $account->id) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'parent_id' =>
                            'Circular parent tidak diperbolehkan.'
                    ]);
            }

            if (!$current->parent_id) {
                break;
            }

            $current = AccountingAccount::find(
                $current->parent_id
            );
        }
    }

    if (!$parentId) {

        $parts = explode(
            '-',
            $request->account_code
        );

        $isRoot =
            isset($parts[1]) &&
            $parts[1] === '000' &&
            isset($parts[2]) &&
            $parts[2] === '000';


        if (!$isRoot) {

            return back()
                ->withInput()
                ->withErrors([
                    'parent_id' =>
                        'Akun ini wajib memiliki akun induk.'
                ]);
        }
    }

    if ($parentId) {

        $descendantIds = [];

        $children = AccountingAccount::where(
            'parent_id',
            $account->id
        )->get();


        $collectChildren = function ($items) use (
            &$collectChildren,
            &$descendantIds
        ) {

            foreach ($items as $item) {

                $descendantIds[] = $item->id;

                $children = AccountingAccount::where(
                    'parent_id',
                    $item->id
                )->get();

                $collectChildren($children);
            }
        };

        $collectChildren($children);


        if (in_array(
            $parentId,
            $descendantIds
        )) {

            return back()
                ->withInput()
                ->withErrors([
                    'parent_id' =>
                        'Tidak boleh memilih akun anak sebagai parent.'
                ]);
        }
    }

    $account->update([

        'account_code' =>
            $request->account_code,

        'account_name' =>
            $request->account_name,

        'category' =>
            $request->category,

        'sub_category' =>
            $request->sub_category,

        'initial_balance' =>
            $isParent
                ? null
                : ($request->initial_balance ?? 0),

        'is_parent' =>
            $isParent,

        'parent_id' =>
            $parentId,

        'is_active' =>
            true,

        'person_type' =>
            $request->person_type,

        'license_id' =>
            $licenseId,
    ]);


    return redirect()
        ->route('accounting.index')
        ->with(
            'success',
            'Akun berhasil diubah.'
        );
}

    public function destroy($id)
    {
        $account = AccountingAccount::findOrFail($id);
        $account->delete();

        return response()->json(['status' => 'success']);
    }
    public function generateCode(Request $request)
    {
        $category = $request->category;
        $parentId = $request->parent_id;
        $isParent = $request->is_parent;
        $excludeId = $request->exclude_id;

        if (!$category) {
            return response()->json([
                'code' => '-'
            ]);
        }

        $code = $this->generateAccountCode(
            $category,
            $parentId,
            $isParent,
            $excludeId
        );

        return response()->json([
            'code' => $code
        ]);
    }


private function getCategoryPrefix($category)
{
    return match (strtoupper($category)) {
        'AKTIVA' => '1',
        'KEWAJIBAN' => '2',
        'EKUITAS' => '3',
        'PENDAPATAN' => '4',
        'BEBAN' => '5',
        default => '0',
    };
}


private function generateAccountCode(
    $category,
    $parentId = null,
    $isParent = null,
    $excludeId = null
) {
    $prefix = $this->getCategoryPrefix($category);

    if (!$parentId) {

        $rootCode = "{$prefix}-000-000";

        $query = AccountingAccount::where(
            'account_code',
            $rootCode
        );

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $root = $query->first();

        if (!$root) {
            return $rootCode;
        }

        return null;
    }

    $parent = AccountingAccount::findOrFail($parentId);

    $parts = explode(
        '-',
        $parent->account_code
    );

    $parentPrefix = $parts[0];
    $parentMid    = $parts[1];

    if ($isParent == '1') {

        $query = AccountingAccount::where(
            'parent_id',
            $parentId
        )
        ->where('is_parent', true)
        ->orderBy('account_code', 'desc');

        if ($excludeId) {
            $query->where(
                'id',
                '!=',
                $excludeId
            );
        }

        $last = $query->first();

        if ($last) {

            $lastParts = explode(
                '-',
                $last->account_code
            );

            $lastMid = (int) $lastParts[1];

            $nextMid = str_pad(
                $lastMid + 1,
                3,
                '0',
                STR_PAD_LEFT
            );

        } else {

            $nextMid = '100';
        }


        return "{$parentPrefix}-{$nextMid}-000";
    }

    $query = AccountingAccount::where(
        'parent_id',
        $parentId
    )
    ->orderBy('account_code', 'desc');

    if ($excludeId) {
        $query->where(
            'id',
            '!=',
            $excludeId
        );
    }


    $last = $query->first();

    if ($last) {

        $lastEnd = (int) substr(
            $last->account_code,
            -3
        );

        $nextEnd = str_pad(
            $lastEnd + 1,
            3,
            '0',
            STR_PAD_LEFT
        );

    } else {

        $nextEnd = '001';
    }


    return "{$parentPrefix}-{$parentMid}-{$nextEnd}";
}

private function generateSubCategoryCode($subCategory)
{
    return match ($subCategory) {
        'Aset Lancar - Kas & Bank' => '100',
        'Aset Lancar - Piutang' => '110',
        'Aset Lancar - Persediaan Barang' => '120',
        'Aset Tetap' => '200',

        'Hutang' => '100',
        'Pajak' => '200',

        'Modal' => '100',

        'Pendapatan Desain' => '100',
        'Pendapatan RAB' => '200',
        'Pendapatan Build' => '300',

        'Biaya Desain' => '100',
        'Biaya RAB' => '200',
        'Biaya Build' => '300',

        default => '999',
    };
}

}