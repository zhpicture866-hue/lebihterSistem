<form id="rab-edit-form" action="{{ route('projects.rab.update', [$project->id, $rab->id]) }}" method="POST">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <input type="hidden" name="project_id" value="{{ $project->id }}">

    <h4 class="fw-bold mb-3">Informasi Pembuatan Rab</h4>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Nomor Penawaran</label>
            <input type="text" name="offer_number" class="form-control" value="{{ old('offer_number', $rab->offer_number) ?? '' }}" placeholder="Auto Generate" readonly>

        </div>
        <div class="col-md-4">

            <label class="form-label fw-semibold">
                Tanggal Penawaran
            </label>

            <div class="input-icon">
                <span class="input-icon-addon">
                    <i class="ti ti-calendar"></i>
                </span>

                <input type="text"
                    name="offer_date"
                    id="offer_date"
                    class="form-control"
                    placeholder="dd/mm/yyyy"
                    value="{{ old('offer_date', $rab->offer_date?->format('Y-m-d')) }}">

            </div>

        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Nama Customer</label>
            <input type="text" name="contact_name" value="{{ $rab->contact_name }}" class="form-control" readonly>
        </div>
    </div>
  
    <div class="row mb-4 mt-3">
        <div class="rab-detail-header mb-3">

            <h4 class="fw-bold mb-0">
                Rincian Pekerjaan
            </h4>

            <div class="rab-action-buttons">

                {{-- <button type="button"
                        id="tombolUbahh"
                        class="btn btn-dark btn-sm">
                    ✏️ Mode Edit
                </button>

                <button type="button"
                        id="tombolGeserr"
                        class="btn btn-outline-secondary btn-sm">
                    🔀 Urutkan Daftar Pekerjaan
                </button> --}}
                <button type="button"
                        class="btn btn-dark btn-sm"
                            onclick="openEditRabItemModal()">
                    + Tambah Item
                </button>
            </div>

        </div>
        <div class="table-responsive">

            <table class="table table-bordered align-middle" id="rabItemsTable">

                <colgroup>
                    <col style="width: 60px">
                    <col style="width: 180px">
                    <col style="width: 130px">
                    <col style="width: 60px">
                    <col style="width: 130px">
                    <col style="width: 180px">
                    <col style="width: 90px">
                </colgroup>

                <thead>
                    <tr>
                        <th class="text-center">NO</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Periode</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Harga</th>
                        <th class="text-center">JUMLAH</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody id="rab_offerItemsBody_edit"></tbody>

                <tfoot>

                    <tr>
                        <th colspan="5" class="text-end">
                            SUBTOTAL
                        </th>

                        <th id="rab_subtotalDisplay_edit">
                            Rp 0
                        </th>

                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="5" class="text-end">
                            DISCOUNT
                        </th>

                        <th>
                            <input type="text"
                                class="form-control"
                                id="rab_discount_display_edit">

                            <input type="hidden"
                                name="discount"
                                id="rab_discount"
                                value="{{ old('discount', (float) $rab->discount) }}">
                        </th>

                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="5" class="text-end">
                            SUBTOTAL AFTER DISCOUNT
                        </th>

                        <th id="rab_subAfterDiscountDisplay_edit">
                            Rp 0
                        </th>

                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="5" class="text-end">
                            TAX RATE (%)
                        </th>

                        <th>
                            <input type="number"
                                class="form-control"
                                name="tax_rate"
                                id="rab_tax_rate_edit"
                                min="0"
                                step="0.01"
                                value="{{ old('tax_rate', (float) $rab->tax_rate) }}">
                        </th>

                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="5" class="text-end">
                            TOTAL TAX
                        </th>

                        <th id="rab_totalTaxDisplay_edit">
                            Rp 0
                        </th>

                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="5" class="text-end">
                            SHIPPING / HANDLING
                        </th>

                        <th>
                            <input type="text"
                                class="form-control"
                                id="rab_shipping_display_edit">

                            <input type="hidden"
                                name="shipping"
                                id="rab_shipping"
                                value="{{ old('shipping', (float) $rab->shipping) }}">
                        </th>

                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="5" class="text-end">
                            GRAND TOTAL
                        </th>

                        <th id="rab_grandTotalDisplay_edit">
                            Rp 0
                        </th>

                        <th></th>
                    </tr>

                </tfoot>

            </table>

        </div>
    </div>
    <div class="modal fade" id="editRabItemModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header border-0">

                    <div>
                        <h5 class="modal-title fw-bold">
                            Tambah Item RAB
                        </h5>

                        <small class="text-muted">
                            Masukkan produk yang akan ditambahkan ke RAB
                        </small>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>


                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Deskripsi Pekerjaan
                        </label>

                        <div id="edit-description-editor"></div>

                        <textarea id="rab_item_description_edit"
                                class="d-none"></textarea>

                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 mb-3">

                            <label class="form-label required fw-semibold">
                                Periode Berlangganan
                            </label>

                            <select id="rab_item_billing_period_edit"
                                    class="form-select">
                                <option value="monthly">Bulanan (Monthly)</option>
                                <option value="annual">Tahunan (Annual)</option>
                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label required fw-semibold">
                                Qty
                                <small class="text-muted fw-normal" id="rab_item_qty_hint_edit">(jumlah bulan)</small>
                            </label>

                            <input type="text"
                                id="rab_item_volume_edit"
                                class="form-control"
                                inputmode="decimal"
                                placeholder="1">

                        </div>

                        <div class="col-md-12 mb-3">

                            <label class="form-label required fw-semibold">
                                Harga
                                <small class="text-muted fw-normal" id="rab_item_price_hint_edit">(per bulan)</small>
                            </label>

                            <input type="text"
                                id="rab_item_price_display_edit"
                                class="form-control"
                                inputmode="decimal"
                                placeholder="Rp 0,00">

                            <input type="hidden"
                                id="edit_rab_item_price">

                        </div>
                    </div>
                </div>


                <div class="modal-footer border-0">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="button"
                            class="btn btn-dark"
                            onclick="saveEditRabItem()">
                        Simpan Item
                    </button>

                </div>

            </div>

        </div>

    </div>
        <input type="hidden" name="subtotal" id="rab_subtotal" value="{{ $rab->subtotal }}">
        <input type="hidden" name="subtotal_after_discount" id="rab_subAfterDiscount" value="{{ $rab->subtotal_after_discount }}">
        <input type="hidden" name="tax_total" id="rab_tax_total" value="{{ $rab->tax_total }}">
        <input type="hidden" name="grand_total" id="rab_grand_total" value="{{ $rab->grand_total }}">
        <input type="hidden" name="profit"   id="rab_profit_edit"   value="{{ old('profit', $rab->profit) }}">
        <input type="hidden" name="overhead" id="rab_overhead_edit" value="{{ old('overhead', $rab->overhead) }}">
            <div id="rabEditItemsContainer"></div>
    <h4 class="fw-bold mb-3">Keterangan</h4>

    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $rab->notes) }}</textarea>
</form>

@push('js')
@php
    // Sumber data: input lama (jika validasi gagal) atau data dari database
    $rawItems = old('items') !== null
        ? array_values(old('items'))
        : $rab->items->sortBy('order_no')->values()->map->toArray()->all();

    $initialItems = [];

    foreach ($rawItems as $i => $row) {
        $basePrice = (float) ($row['base_price'] ?? 0);
        $period    = $row['billing_period'] ?? 'monthly';

        $initialItems['job_' . ($i + 1)] = [
            'id'             => $row['id'] ?? null,
            'description'    => $row['description'] ?? '',
            'billing_period' => in_array($period, ['monthly', 'annual'], true) ? $period : 'monthly',
            'volume'         => (float) ($row['volume'] ?? 0),
            'base_price'     => $basePrice,
            'harga'          => (float) ($row['price'] ?? $basePrice),
            'total'          => (float) ($row['total'] ?? 0),
            'order_no'       => $i + 1,
        ];
    }
@endphp
<script>
    window.currentRabId = "{{ $rab->id ?? '' }}";

    const BILLING_PERIODS = {
        monthly: { label: 'Bulanan', unit: 'bulan' },
        annual:  { label: 'Tahunan', unit: 'tahun' }
    };

    // Object (bukan array): key = id baris DOM, mis. "job_1"
    let rabEditItems = Object.assign({}, @json($initialItems));
    let itemCounter = Object.keys(rabEditItems).length;
    let globalProfit = {{ (float) old('profit', $rab->profit ?? 0) }};
    let globalOverhead = {{ (float) old('overhead', $rab->overhead ?? 0) }};
    let editrabDescriptionEditor = null;

    // =========================
    // HELPER
    // =========================
    function normalizeBillingPeriod(value) {
        return BILLING_PERIODS[value] ? value : 'monthly';
    }

    function parseRupiah(val) {
        if (!val) return 0;

        val = String(val).replace(/[^\d.,]/g, '');
        val = val.replace(/\./g, '');
        val = val.replace(',', '.');

        return Number(val) || 0;
    }

    function formatRupiah(value) {
        value = Number(value) || 0;

        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    function parseDecimal(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        let str = String(value).trim().replace(/\s/g, '');

        if (str.includes(',')) {
            str = str.replace(/\./g, '');
            str = str.replace(',', '.');
        }

        return parseFloat(str) || 0;
    }

    function round(num) {
        return Math.round(num);
    }

    function updateEditBillingPeriodHints() {
        const select = document.getElementById('rab_item_billing_period_edit');
        if (!select) return;

        const unit = BILLING_PERIODS[normalizeBillingPeriod(select.value)].unit;

        const qtyHint = document.getElementById('rab_item_qty_hint_edit');
        const priceHint = document.getElementById('rab_item_price_hint_edit');

        if (qtyHint) qtyHint.textContent = `(jumlah ${unit})`;
        if (priceHint) priceHint.textContent = `(per ${unit})`;
    }

    // =========================
    // INPUT RUPIAH (DISCOUNT / SHIPPING / TAX)
    // =========================
    function initRupiahInputsEdit() {

        const discountInput = document.getElementById('rab_discount_display_edit');

        if (discountInput) {
            discountInput.addEventListener('input', function () {
                document.getElementById('rab_discount').value = parseRupiah(this.value);
                rabEditCalculateSummary();
            });

            discountInput.addEventListener('blur', function () {
                const value = parseRupiah(this.value);
                this.value = value > 0 ? formatRupiah(value) : '';
            });
        }

        const shippingInput = document.getElementById('rab_shipping_display_edit');

        if (shippingInput) {
            shippingInput.addEventListener('input', function () {
                document.getElementById('rab_shipping').value = parseRupiah(this.value);
                rabEditCalculateSummary();
            });

            shippingInput.addEventListener('blur', function () {
                const value = parseRupiah(this.value);
                this.value = value > 0 ? formatRupiah(value) : '';
            });
        }

        const taxInput = document.getElementById('rab_tax_rate_edit');

        if (taxInput) {
            taxInput.addEventListener('input', rabEditCalculateSummary);
        }
    }

    // Isi kolom tampilan discount & shipping dari nilai hidden (data database)
    function initEditFormValues() {
        const discount = Number(document.getElementById('rab_discount').value) || 0;
        const shipping = Number(document.getElementById('rab_shipping').value) || 0;

        const discountDisplay = document.getElementById('rab_discount_display_edit');
        const shippingDisplay = document.getElementById('rab_shipping_display_edit');

        if (discountDisplay) {
            discountDisplay.value = discount > 0 ? formatRupiah(discount) : '';
        }

        if (shippingDisplay) {
            shippingDisplay.value = shipping > 0 ? formatRupiah(shipping) : '';
        }
    }

    // =========================
    // PERHITUNGAN
    // =========================
    function calculateItemPriceEdit(basePrice) {
        basePrice = Number(basePrice) || 0;

        return basePrice
            + (basePrice * globalProfit / 100)
            + (basePrice * globalOverhead / 100);
    }

    function rabEditPriceInput(rowId) {
        const row = document.getElementById(rowId);
        if (!row) return;

        const hargaInput = row.querySelector('.harga');
        if (!hargaInput) return;

        hargaInput.dataset.basePrice = parseRupiah(hargaInput.value);

        rabEditCalculate(rowId);
    }

    function formatRabEditPrice(rowId) {
        const row = document.getElementById(rowId);
        if (!row) return;

        const hargaInput = row.querySelector('.harga');
        if (!hargaInput) return;

        const basePrice = parseRupiah(hargaInput.value);

        hargaInput.dataset.basePrice = basePrice;
        hargaInput.value = basePrice ? formatRupiah(basePrice) : '';

        rabEditCalculate(rowId);
    }

    function rabEditCalculate(rowId, updateSummary = true) {
        const row = document.getElementById(rowId);
        if (!row) return;

        const vol = Number(row.querySelector('.vol')?.value) || 0;
        const hargaInput = row.querySelector('.harga');
        const totalEl = row.querySelector('.total');

        if (!hargaInput || !totalEl) return;

        const basePrice = Number(hargaInput.dataset.basePrice) || 0;
        const hargaFinal = calculateItemPriceEdit(basePrice);
        const total = vol * hargaFinal;

        totalEl.dataset.value = total;
        totalEl.value = formatRupiah(total);

        rabEditItems[rowId] = {
            ...(rabEditItems[rowId] || {}),
            volume: vol,
            base_price: basePrice,
            harga: hargaFinal,
            total: total
        };

        if (updateSummary) {
            rabEditCalculateSummary();
        }
    }

    function recalcAllEditRows() {
        document
            .querySelectorAll('#rab_offerItemsBody_edit .job-row')
            .forEach(row => rabEditCalculate(row.id, false));

        rabEditCalculateSummary();
    }

    function rabEditCalculateSummary() {

        let subtotal = 0;

        document.querySelectorAll('#rab_offerItemsBody_edit .total').forEach(el => {
            subtotal += Number(el.dataset.value || 0);
        });

        document.getElementById('rab_subtotal').value = subtotal;
        document.getElementById('rab_subtotalDisplay_edit').innerText = formatRupiah(subtotal);

        const discount = Number(document.getElementById('rab_discount').value || 0);
        const subAfterDiscount = Math.max(0, subtotal - discount);

        document.getElementById('rab_subAfterDiscount').value = subAfterDiscount;
        document.getElementById('rab_subAfterDiscountDisplay_edit').innerText = formatRupiah(subAfterDiscount);

        const taxRate = Number(document.getElementById('rab_tax_rate_edit').value || 0);
        const taxTotal = round(subAfterDiscount * taxRate / 100);

        document.getElementById('rab_tax_total').value = taxTotal;
        document.getElementById('rab_totalTaxDisplay_edit').innerText = formatRupiah(taxTotal);

        const shipping = Number(document.getElementById('rab_shipping').value || 0);
        const grand = subAfterDiscount + taxTotal + shipping;

        const grandEl = document.getElementById('rab_grandTotalDisplay_edit');
        grandEl.dataset.value = grand;
        grandEl.innerText = formatRupiah(grand);

        document.getElementById('rab_grand_total').value = grand;
    }

    // =========================
    // TABEL ITEM
    // =========================
    function buildJobRowHtml(item, jobId, no) {
        const period = normalizeBillingPeriod(item.billing_period);
        const basePrice = Number(item.base_price) || 0;
        const total = Number(item.total) || 0;

        return `
        <tr class="job-row"
            id="${jobId}"
            data-id="${item.id ?? ''}"
            data-order="${item.order_no ?? 0}">

            <td class="text-center">${no}</td>

            <td>
                <div class="rab-description-preview"
                     style="cursor:pointer"
                     onclick="openEditRabItemModal('${jobId}')">
                    ${item.description ?? ''}
                </div>
            </td>

            <td>
                <select class="form-select billing-period"
                        onchange="updateEditItemBillingPeriod('${jobId}', this.value)">
                    <option value="monthly" ${period === 'monthly' ? 'selected' : ''}>Bulanan</option>
                    <option value="annual" ${period === 'annual' ? 'selected' : ''}>Tahunan</option>
                </select>
            </td>

            <td>
                <input type="number" class="form-control vol" step="0.00001" min="0"
                       value="${Number(item.volume) || 0}"
                       oninput="rabEditCalculate('${jobId}')">
            </td>

            <td>
                <input type="text" class="form-control harga"
                       value="${formatRupiah(basePrice)}"
                       data-base-price="${basePrice}"
                       oninput="rabEditPriceInput('${jobId}')"
                       onblur="formatRabEditPrice('${jobId}')">
            </td>

            <td>
                <input type="text" class="form-control total"
                       data-value="${total}"
                       value="${formatRupiah(total)}"
                       readonly>
            </td>

            <td class="text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-dark"
                        onclick="openEditRabItemModal('${jobId}')">✎</button>
                <button type="button" class="btn btn-sm btn-secondary"
                        onclick="removeJob('${jobId}')">-</button>
            </td>
        </tr>`;
    }

    function renderEditRabItems() {
        const tbody = document.getElementById('rab_offerItemsBody_edit');
        if (!tbody) return;

        const entries = Object.entries(rabEditItems);

        if (entries.length === 0) {
            tbody.innerHTML = `
                <tr class="empty-rab-row">
                    <td colspan="7" class="text-center text-muted py-5">
                        Belum ada item penawaran.
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = entries
            .map(([jobId, item], i) => buildJobRowHtml(item, jobId, i + 1))
            .join('');
    }

    function updateEditItemBillingPeriod(jobId, value) {
        if (!rabEditItems[jobId]) return;

        rabEditItems[jobId].billing_period = normalizeBillingPeriod(value);
    }

    function removeJob(id) {
        delete rabEditItems[id];
        renderEditRabItems();
        rabEditCalculateSummary();
    }

    // =========================
    // MODAL TAMBAH / EDIT ITEM
    // =========================
    function openEditRabItemModal(jobId = null) {
        const isEdit = !!jobId;
        const item = isEdit ? rabEditItems[jobId] : null;

        if (isEdit && !item) {
            console.error('Item penawaran tidak ditemukan:', jobId);
            return;
        }

        const modalElement = document.getElementById('editRabItemModal');
        modalElement.dataset.jobId = jobId || '';

        const description = item?.description || '';

        if (editrabDescriptionEditor) {
            editrabDescriptionEditor.clipboard.dangerouslyPasteHTML(description);
        }

        document.getElementById('rab_item_description_edit').value = description;
        document.getElementById('rab_item_volume_edit').value = item?.volume ?? '';
        document.getElementById('rab_item_billing_period_edit').value =
            normalizeBillingPeriod(item?.billing_period);

        updateEditBillingPeriodHints();

        const price = document.getElementById('rab_item_price_display_edit');
        const numericPrice = parseFloat(item?.base_price ?? item?.harga) || 0;

        price.value = numericPrice > 0 ? formatRupiah(numericPrice) : '';
        price.dataset.value = numericPrice;
        document.getElementById('edit_rab_item_price').value = numericPrice;

        modalElement.querySelector('.modal-title').textContent =
            isEdit ? 'Edit Item Penawaran' : 'Tambah Item Penawaran';

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    function saveEditRabItem() {
        const modalElement = document.getElementById('editRabItemModal');
        const jobId = modalElement.dataset.jobId;

        const isEmpty = editrabDescriptionEditor.getText().trim().length === 0;
        const description = isEmpty ? '' : editrabDescriptionEditor.root.innerHTML;
        const volume = parseDecimal(document.getElementById('rab_item_volume_edit').value);
        const basePrice = parseRupiah(document.getElementById('rab_item_price_display_edit').value);
        const billingPeriod = normalizeBillingPeriod(
            document.getElementById('rab_item_billing_period_edit').value
        );

        if (!description) { alert('Deskripsi wajib diisi.'); return; }
        if (volume <= 0) { alert('Qty harus lebih besar dari 0.'); return; }
        if (basePrice < 0) { alert('Harga tidak valid.'); return; }

        const price = calculateItemPriceEdit(basePrice);
        const total = volume * price;

        if (jobId && rabEditItems[jobId]) {
            rabEditItems[jobId] = {
                ...rabEditItems[jobId],
                description,
                billing_period: billingPeriod,
                volume,
                base_price: basePrice,
                harga: price,
                total
            };
        } else {
            const newId = 'job_new_' + (++itemCounter);

            rabEditItems[newId] = {
                id: null,
                description,
                billing_period: billingPeriod,
                volume,
                base_price: basePrice,
                harga: price,
                total,
                order_no: Object.keys(rabEditItems).length + 1
            };
        }

        renderEditRabItems();
        rabEditCalculateSummary();
        bootstrap.Modal.getInstance(modalElement)?.hide();
    }

    // =========================
    // SUBMIT
    // =========================
    function prepareRabEditItemsForSubmit() {

        document.getElementById('rab_profit_edit').value = globalProfit;
        document.getElementById('rab_overhead_edit').value = globalOverhead;

        const container = document.getElementById('rabEditItemsContainer');

        if (!container) {
            console.error('rabEditItemsContainer tidak ditemukan');
            return 0;
        }

        container.innerHTML = '';

        // hitung ulang semua baris sesuai kondisi DOM saat ini
        recalcAllEditRows();

        let index = 0;

        document.querySelectorAll('#rab_offerItemsBody_edit .job-row').forEach(row => {

            const item = rabEditItems[row.id];
            if (!item) return;

            const fields = {
                id: item.id ?? '',
                description: item.description || '',
                billing_period: normalizeBillingPeriod(row.querySelector('.billing-period')?.value),
                volume: Number(row.querySelector('.vol')?.value) || 0,
                base_price: Number(row.querySelector('.harga')?.dataset.basePrice) || 0,
                price: item.harga ?? 0,
                total: Number(row.querySelector('.total')?.dataset.value) || 0,
                order_no: index + 1
            };

            Object.entries(fields).forEach(([key, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${index}][${key}]`;
                input.value = value ?? '';
                container.appendChild(input);
            });

            index++;
        });

        return index;
    }

    // =========================
    // KOMPATIBILITAS (jika masih dipanggil dari file lain)
    // =========================
    function initRabEdit() {
        renderEditRabItems();
        recalcAllEditRows();
    }

    function loadExistingRab(data) {
        globalProfit = parseFloat(data.meta?.profit) || 0;
        globalOverhead = parseFloat(data.meta?.overhead) || 0;

        document.getElementById('rab_discount').value = parseFloat(data.meta?.discount) || 0;
        document.getElementById('rab_shipping').value = parseFloat(data.meta?.shipping) || 0;
        document.getElementById('rab_tax_rate_edit').value = parseFloat(data.meta?.tax_rate) || 0;
        initEditFormValues();

        rabEditItems = {};

        (Array.isArray(data.items) ? [...data.items] : [])
            .sort((a, b) => (a.order_no ?? 0) - (b.order_no ?? 0))
            .forEach((item, i) => {
                const basePrice = parseFloat(item.base_price) || 0;

                rabEditItems['job_' + (i + 1)] = {
                    id: item.id ?? null,
                    description: item.description ?? '',
                    billing_period: normalizeBillingPeriod(item.billing_period),
                    volume: parseFloat(item.volume) || 0,
                    base_price: basePrice,
                    harga: parseFloat(item.price) || basePrice,
                    total: parseFloat(item.total) || 0,
                    order_no: i + 1
                };
            });

        itemCounter = Object.keys(rabEditItems).length;

        renderEditRabItems();
        recalcAllEditRows();
    }

    // =========================
    // INIT
    // =========================
    document.addEventListener('DOMContentLoaded', function () {

        const editorEl = document.getElementById('edit-description-editor');

        if (editorEl) {
            editrabDescriptionEditor = new Quill('#edit-description-editor', {
                theme: 'snow',
                placeholder: 'Tuliskan deskripsi produk...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            editrabDescriptionEditor.on('text-change', function () {
                const empty = editrabDescriptionEditor.getText().trim().length === 0;
                document.getElementById('rab_item_description_edit').value =
                    empty ? '' : editrabDescriptionEditor.root.innerHTML;
            });
        }

        const offerDate = document.getElementById('offer_date');

        if (offerDate) {
            flatpickr(offerDate, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: true,
                defaultDate: offerDate.value || new Date()
            });
        }

        initRupiahInputsEdit();
        initEditFormValues();

        const periodSelect = document.getElementById('rab_item_billing_period_edit');

        if (periodSelect) {
            periodSelect.addEventListener('change', updateEditBillingPeriodHints);
        }

        const rabEditForm = document.getElementById('rab-edit-form');

        if (rabEditForm) {
            rabEditForm.addEventListener('submit', function (e) {
                const count = prepareRabEditItemsForSubmit();

                if (count === 0) {
                    e.preventDefault();
                    alert('Minimal harus ada 1 item penawaran.');
                }
            });
        }

        renderEditRabItems();
        recalcAllEditRows();
    });
</script>
@endpush