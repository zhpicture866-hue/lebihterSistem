@can('lihat daftar proyek')
<form id="rabForm" action="{{ route('projects.rab.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
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

    {{-- <h4 class="fw-bold mb-3">Informasi Pembuatan Rab</h4> --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <label>Nomor Penawaran</label>
            <input type="text" name="offer_number" class="form-control" value="{{ old('offer_number') ?? '' }}" placeholder="Auto Generate" readonly>

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
                    value="{{ old('offer_date', now()->format('Y-m-d')) }}">

            </div>

        </div>
        <div class="col-md-4">
            <label>Nama Customer</label>
            <input type="text" value="{{ $project->customer->user->fullname }}" class="form-control" readonly>
        </div>
    </div>
  
    <div class="row mb-4 mt-3">

        <div class="rab-detail-header mb-3">

            <h4 class="fw-bold mb-0">
                Rincian Pekerjaan
            </h4>

            <div class="rab-action-buttons">

                {{-- <button type="button"
                        id="tombolUbah"
                        class="btn btn-dark btn-sm">
                    ✏️ Mode Edit
                </button>

                <button type="button"
                        id="tombolGeser"
                        class="btn btn-outline-secondary btn-sm">
                    🔀 Urutkan Daftar Pekerjaan
                </button> --}}

                <button type="button"
                        class="btn btn-dark btn-sm"
                        onclick="openAddRabItemModal()">
                    + Tambah Item
                </button>
            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered align-middle" id="rabItemsTable">

                <colgroup>
                    <col style="width: 60px">
                    <col style="width: 180px">
                    <col style="width: 60px">
                    <col style="width: 130px">
                    <col style="width: 180px">
                    <col style="width: 40px">
                </colgroup>

                <thead>
                    <tr>
                        <th class="text-center">NO</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Harga</th>
                        <th class="text-center">JUMLAH</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody id="rab_offerItemsBody"></tbody>

                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">
                            SUBTOTAL
                        </th>

                        <th id="rab_subtotalDisplay">
                            Rp 0
                        </th>
                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="4" class="text-end">
                            DISCOUNT
                        </th>

                        <th>
                            <input type="text"
                                class="form-control"
                                id="rab_discount_display">

                            <input type="hidden"
                                name="discount"
                                id="rab_discount">
                        </th>
                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="4" class="text-end">
                            SUBTOTAL AFTER DISCOUNT
                        </th>

                        <th id="rab_subAfterDiscountDisplay">
                            Rp 0
                        </th>
                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="4" class="text-end">
                            TAX RATE (%)
                        </th>

                        <th>
                            <input type="number"
                                class="form-control"
                                name="tax_rate"
                                id="rab_tax_rate"
                                min="0"
                                step="0.01">
                        </th>
                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="4" class="text-end">
                            TOTAL TAX
                        </th>

                        <th id="rab_totalTaxDisplay">
                            Rp 0
                        </th>
                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="4" class="text-end">
                            SHIPPING / HANDLING
                        </th>

                        <th>
                            <input type="text"
                                class="form-control"
                                id="rab_shipping_display">

                            <input type="hidden"
                                name="shipping"
                                id="rab_shipping">
                        </th>
                        <th></th>
                    </tr>

                    <tr>
                        <th colspan="4" class="text-end">
                            GRAND TOTAL
                        </th>

                        <th id="rab_grandTotalDisplay">
                            Rp 0
                        </th>
                        <th></th>
                    </tr>

                </tfoot>

            </table>

        </div>

    </div>
    <div class="modal fade" id="addRabItemModal" tabindex="-1" aria-hidden="true">

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

                        <div id="description-editor"></div>

                        <textarea id="rab_item_description"
                                class="d-none"></textarea>

                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 mb-3">

                            <label class="form-label required fw-semibold">
                                Qty
                            </label>

                            <input type="text"
                                id="rab_item_volume"
                                class="form-control"
                                inputmode="decimal"
                                placeholder="1">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label required fw-semibold">
                                Harga
                            </label>

                            <input type="text"
                                id="rab_item_price_display"
                                class="form-control"
                                inputmode="decimal"
                                placeholder="Rp 0,00">

                            <input type="hidden"
                                id="rab_item_price">

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
                            onclick="saveRabItem()">
                        Simpan Item
                    </button>

                </div>

            </div>

        </div>

    </div>
        <input type="hidden" name="profit" id="rab_profit">
        <input type="hidden" name="overhead" id="rab_overhead">
        <input type="hidden" name="subtotal" id="rab_subtotal">
        <input type="hidden" name="subtotal_after_discount" id="rab_subAfterDiscount">
        <input type="hidden" name="tax_total" id="rab_tax_total">
        <input type="hidden" name="grand_total" id="rab_grand_total">                  
    <div id="rabItemsContainer"></div>
    <h4 class="fw-bold mb-3">Keterangan</h4>

    <textarea name="notes" rows="3" class="form-control"></textarea>
</form>
@endcan

@push('js')
<script>

    let rabItems = [];
    let currentMode = 'edit';
    let sortableInstance = null;
    let itemCounter = 0;
    let importedRabItems = [];

    let rabDescriptionEditor = null;

    document.addEventListener('DOMContentLoaded', function () {

        rabDescriptionEditor = new Quill('#description-editor', {
            theme: 'snow',

            placeholder: 'Tuliskan deskripsi produk...',

            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [
                        { list: 'ordered' },
                        { list: 'bullet' }
                    ],
                    ['link'],
                    ['clean']
                ]
            }
        });
    const offerDate =
        document.getElementById('offer_date');

    if (offerDate) {

        flatpickr(offerDate, {

            dateFormat: 'Y-m-d',

            altInput: true,

            altFormat: 'd/m/Y',

            allowInput: true,

            defaultDate:
                offerDate.value || new Date(),

        });

    }
    });

    async function handleRabExcelFile(event) {

        const file = event.target.files[0];

        if (!file) {
            return;
        }

        const errorElement = document.getElementById('rabImportError');

        // const previewElement = document.getElementById('rabImportPreview');

        const confirmButton = document.getElementById('btnConfirmImportRab');

        // if (!previewElement) {
        //     console.error(
        //         '#rabImportPreview tidak ditemukan.'
        //     );
        //     return;
        // }

        if (errorElement) {
            errorElement.classList.add('d-none');
            errorElement.innerHTML = '';
        }

        if (confirmButton) {
            confirmButton.disabled = true;
        }

        // previewElement.innerHTML = `
        //     <div class="text-muted">
        //         Membaca file Excel...
        //     </div>
        // `;

        try {

            const buffer = await file.arrayBuffer();

            const workbook =
                XLSX.read(buffer, {
                    type: 'array'
                });

            if (!workbook.SheetNames.length) {

                throw new Error(
                    'File Excel tidak memiliki sheet.'
                );

            }

            const firstSheetName =
                workbook.SheetNames.find(
                    name => normalizeExcelHeader(name) === 'rab'
                ) ||
                workbook.SheetNames[1] ||
                workbook.SheetNames[0];

            const worksheet = workbook.Sheets[firstSheetName];

            console.log('Sheet yang dipakai:', firstSheetName);

            importedRabItems = validateRabExcelWorksheet(worksheet);

            // renderRabImportPreview(
            //     importedRabItems
            // );

            if (confirmButton) {

                confirmButton.disabled =
                    importedRabItems.length === 0;
            }

        } catch (error) {
            console.error(
                'Error import Excel:',
                error
            );

            importedRabItems = [];

            if (errorElement) {

                errorElement.innerHTML =
                    escapeHtml(
                        error.message ||
                        'Terjadi kesalahan saat membaca file Excel.'
                    );

                errorElement.classList.remove(
                    'd-none'
                );

            }

            // previewElement.innerHTML = `
            //     <div class="alert alert-danger mb-0">
            //         ${escapeHtml(
            //             error.message ||
            //             'Terjadi kesalahan saat membaca file Excel.'
            //         )}
            //     </div>
            // `;

            if (confirmButton) {
                confirmButton.disabled = true;
            }

        }
    }

    function resolveMergedCellValue(worksheet, colLetter, rowNumber) {

        if (!colLetter) {
            return undefined;
        }

        const directCell =
            worksheet[`${colLetter}${rowNumber}`];

        if (directCell && directCell.v !== undefined && directCell.v !== '') {
            return directCell.v;
        }

        const merges = worksheet['!merges'] || [];

        const colIndex =
            XLSX.utils.decode_col(colLetter);

        const rowIndex =
            rowNumber - 1;

        for (const merge of merges) {

            const withinRow =
                rowIndex >= merge.s.r &&
                rowIndex <= merge.e.r;

            const withinCol =
                colIndex >= merge.s.c &&
                colIndex <= merge.e.c;

            if (withinRow && withinCol) {

                const anchorCol =
                    XLSX.utils.encode_col(merge.s.c);

                const anchorRow =
                    merge.s.r + 1;

                const anchorCell =
                    worksheet[`${anchorCol}${anchorRow}`];

                return anchorCell?.v;
            }
        }

        return directCell?.v;
    }

    function getUraianCellValue(worksheet, excelRow, columns) {

        const startColIndex =
            XLSX.utils.decode_col(columns.uraian);

        const boundaryColIndexes = [];

        if (columns.satuan) {
            boundaryColIndexes.push(
                XLSX.utils.decode_col(columns.satuan)
            );
        }

        if (columns.volume) {
            boundaryColIndexes.push(
                XLSX.utils.decode_col(columns.volume)
            );
        }

        const endColIndex =
            boundaryColIndexes.length
                ? Math.min(...boundaryColIndexes) - 1
                : startColIndex + 5;

        for (
            let colIndex = startColIndex;
            colIndex <= endColIndex;
            colIndex++
        ) {

            const colLetter =
                XLSX.utils.encode_col(colIndex);

            const value =
                resolveMergedCellValue(
                    worksheet,
                    colLetter,
                    excelRow
                );

            if (
                value !== undefined &&
                value !== null &&
                String(value).trim() !== ''
            ) {
                return value;
            }
        }

        return '';
    }

    function normalizeExcelHeader(value) {

        return String(value ?? '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '_');
    }
    function normalizeExcelCell(value) {

        return String(value ?? '')
            .replace(/\u00A0/g, ' ')
            .replace(/\r?\n/g, ' ')
            .replace(/\s+/g, ' ')
            .trim()
            .toUpperCase();

    }

    function findRabColumnMap(worksheet, range) {

        const headerAliases = {
            no:      ['no'],
            uraian:  ['uraian_pekerjaan', 'uraian', 'pekerjaan', 'uraian_pekerjaan.'],
            satuan:  ['sat', 'satuan'],
            volume:  ['vol', 'volume'],
            harga:   ['harga_bahan', 'harga_satuan', 'harga'],
            jumlah:  ['jumlah_harga', 'jumlah', 'total_harga', 'total']
        };

        const maxHeaderScanRow =
            Math.min(range.e.r, range.s.r + 100); // batasi scan 100 baris pertama saja

        for (
            let rowIndex = range.s.r;
            rowIndex <= maxHeaderScanRow;
            rowIndex++
        ) {

            const excelRow = rowIndex + 1;
            const found = {};

            for (
                let colIndex = range.s.c;
                colIndex <= range.e.c;
                colIndex++
            ) {

                const colLetter = XLSX.utils.encode_col(colIndex);

                const cell = worksheet[`${colLetter}${excelRow}`];

                const normalized = normalizeExcelHeader(cell?.v);

                if (!normalized) {
                    continue;
                }

                for (const [key, aliases] of Object.entries(headerAliases)) {

                    if (found[key]) {
                        continue; // Kolom untuk key ini sudah ketemu, jangan ditimpa
                    }

                    const isMatch = aliases.some(alias =>
                        normalized === alias ||
                        normalized.includes(alias)
                    );

                    if (isMatch) {
                        found[key] = colLetter;
                    }
                }
            }

            if (
                found.no &&
                found.uraian &&
                found.volume &&
                found.harga
            ) {

                console.log(
                    `Header RAB ditemukan di baris ${excelRow}:`,
                    found
                );

                return {
                    headerRow: excelRow,
                    columns: {
                        no: found.no,
                        uraian: found.uraian,
                        satuan: found.satuan || null,
                        volume: found.volume,
                        harga: found.harga,
                        jumlah: found.jumlah || null
                    }
                };
            }
        }

        throw new Error(
            'Header Excel (NO, URAIAN PEKERJAAN, SAT, VOL, HARGA) tidak ' +
            'ditemukan pada 100 baris pertama. Pastikan format file sesuai ' +
            'template RAB.'
        );
    }

    function validateRabExcelWorksheet(worksheet) {

        const result = [];

        let currentFloor = '';
        let currentCategory = '';
        let currentJobType = '';

        const range = XLSX.utils.decode_range(
            worksheet['!ref']
        );

        const { headerRow, columns } = findRabColumnMap(worksheet, range);
        console.log('Kolom yang terdeteksi:', columns);
        console.log('Baris header:', headerRow);

        for (
            let rowIndex = range.s.r;
            rowIndex <= range.e.r;
            rowIndex++
        ) {

            const excelRow = rowIndex + 1;

            // Lewati semua baris judul/deskripsi sebelum dan termasuk baris header
            if (excelRow <= headerRow) {
                continue;
            }

            const getCellValue = (column) => {

                if (!column) {
                    return '';
                }

                return resolveMergedCellValue(
                    worksheet,
                    column,
                    excelRow
                ) ?? '';

            };

            const no =
                String(
                    getCellValue(columns.no)
                ).trim();

            const uraian =
                String(
                    getUraianCellValue(worksheet, excelRow, columns)
                ).trim();

            const satuan =
                String(
                    getCellValue(columns.satuan)
                ).trim();

            const volumeRaw =
                getCellValue(columns.volume);

            const hargaRaw =
                getCellValue(columns.harga);

            console.log(
                `Excel row ${excelRow}:`,
                {
                    no,
                    uraian,
                    satuan,
                    volumeRaw,
                    hargaRaw
                }
            );

            if (
                !no &&
                !uraian &&
                !satuan &&
                volumeRaw === '' &&
                hargaRaw === ''
            ) {

                continue;

            }

            const normalizedNo =
                no.toUpperCase();

            const normalizedUraian =
                uraian
                    .toUpperCase()
                    .replace(/\s+/g, ' ')
                    .trim();

            const normalizedSatuan =
                satuan.toUpperCase();

            const normalizedVolume =
                String(volumeRaw)
                    .toUpperCase()
                    .trim();


            if (
                normalizedNo === 'NO' ||
                normalizedUraian === 'URAIAN PEKERJAAN' ||
                normalizedSatuan === 'SAT' ||
                normalizedVolume === 'VOL'
            ) {

                console.log(
                    `Skip header row ${excelRow}`
                );

                continue;

            }

            const floorText =
                no || uraian;


            if (
                /^LANTAI\s+/i.test(
                    floorText
                )
            ) {

                currentFloor =
                    floorText.trim();

                currentCategory = '';
                currentJobType = '';

                console.log(
                    'LANTAI:',
                    currentFloor
                );

                continue;

            }

            if (
                /^[A-Z]+$/.test(no) &&
                uraian
            ) {

                currentCategory =
                    uraian.trim();

                currentJobType = '';

                console.log(
                    'KATEGORI:',
                    currentCategory
                );

                continue;

            }

            const isNumberNo =
                /^\d+$/.test(no);


            const hasNoSatuan =
                !satuan;


            const hasNoVolume =
                volumeRaw === '' ||
                volumeRaw === null ||
                volumeRaw === undefined;


            if (
                isNumberNo &&
                uraian &&
                hasNoSatuan &&
                hasNoVolume
            ) {

                currentJobType =
                    uraian.trim();

                console.log(
                    'TIPE PEKERJAAN:',
                    currentJobType
                );

                continue;

            }

            const hasUraian =
                !!uraian;

            const hasSatuan =
                !!satuan;

            const hasVolume =
                volumeRaw !== '' &&
                volumeRaw !== null &&
                volumeRaw !== undefined;


            if (
                !hasUraian ||
                !hasSatuan ||
                !hasVolume
            ) {

                continue;

            }

            if (isNumberNo) {
                currentJobType = '';
            }

            if (!currentFloor) {

                throw new Error(
                    `Baris ${excelRow}: Lantai belum ditemukan.`
                );

            }

            if (!currentCategory) {

                throw new Error(
                    `Baris ${excelRow}: Kategori belum ditemukan untuk "${uraian}".`
                );

            }

            const volume =
                parseExcelNumber(
                    volumeRaw
                );

            const basePrice =
                parseExcelNumber(
                    hargaRaw
                );

            if (
                !Number.isFinite(volume) ||
                volume <= 0
            ) {

                throw new Error(
                    `Baris ${excelRow}: Volume "${uraian}" tidak valid.`
                );

            }

            if (
                !Number.isFinite(basePrice) ||
                basePrice < 0
            ) {

                throw new Error(
                    `Baris ${excelRow}: Harga satuan "${uraian}" tidak valid.`
                );

            }

            const price =
                calculateItemPrice(
                    basePrice
                );

            const total =
                volume * price;

            result.push({

                temp_id:
                    'item_' +
                    (++itemCounter),

                floor_name:
                    currentFloor,

                category_name:
                    currentCategory,

                job_name:
                    uraian,

                description:
                    currentJobType || '',

                satuan:
                    satuan,

                volume:
                    volume,

                base_price:
                    basePrice,

                price:
                    price,

                total:
                    total,

                order_no:
                    rabItems.length +
                    result.length +
                    1

            });


            console.log(
                'ITEM IMPORT:',
                result[result.length - 1]
            );

        }

        if (!result.length) {

            throw new Error(
                'Tidak ditemukan detail item pekerjaan pada Excel.'
            );

        }


        console.log(
            'TOTAL ITEM IMPORT:',
            result.length
        );


        return result;
    }
    function parseExcelNumber(value) {

        if (typeof value === 'number') {
            return value;
        }

        if (value === null ||
            value === undefined ||
            value === '') {

            return 0;
        }

        let str =
            String(value)
                .trim()
                .replace(/Rp/gi, '')
                .replace(/\s/g, '');

        // Format Indonesia: 1.500.000,50
        if (str.includes(',') &&
            str.includes('.')) {

            str = str
                .replace(/\./g, '')
                .replace(',', '.');

        } else if (str.includes(',')) {

            str = str.replace(',', '.');

        } else {

            // Angka seperti 1.500.000
            if (
                /^\d{1,3}(\.\d{3})+$/.test(str)
            ) {
                str = str.replace(/\./g, '');
            }
        }

        const number =
            parseFloat(str);

        return Number.isFinite(number)
            ? number
            : 0;
    }

// function renderRabImportPreview(items) {

//     const container =
//         document.getElementById('rabImportPreview');

//     if (!items.length) {
//         container.innerHTML =
//             '<div class="alert alert-warning">' +
//             'Tidak ada item yang dapat diimport.' +
//             '</div>';

//         return;
//     }

//     let html = `
//         <div class="mb-2">
//             <strong>${items.length}</strong>
//             item siap diimport.
//         </div>

//         <table class="table table-sm table-bordered align-middle">

//             <thead>
//                 <tr>
//                     <th>No</th>
//                     <th>Lantai</th>
//                     <th>Kategori</th>
//                     <th>Tipe Pekerjaan</th>
//                     <th>Pekerjaan</th>
//                     <th>Volume</th>
//                     <th>Satuan</th>
//                     <th class="text-end">
//                         Harga Satuan
//                     </th>
//                 </tr>
//             </thead>

//             <tbody>
//     `;

//     items.forEach((item, index) => {

//         html += `
//             <tr>
//                 <td>${index + 1}</td>
//                 <td>${escapeHtml(item.floor_name)}</td>
//                 <td>${escapeHtml(item.category_name)}</td>
//                 <td>${escapeHtml(item.description || '-')}</td>
//                 <td>${escapeHtml(item.job_name)}</td>
//                 <td>${item.volume}</td>
//                 <td>${escapeHtml(item.satuan)}</td>
//                 <td class="text-end">
//                     ${formatRupiah(item.base_price)}
//                 </td>
//             </tr>
//         `;

//     });

//     html += `
//             </tbody>

//         </table>
//     `;

//     container.innerHTML = html;
// }

    function importRabFromExcel() {

        if (!importedRabItems.length) {
            alert('Tidak ada data yang dapat diimport.');
            return;
        }

        rabItems.push(
            ...importedRabItems
        );

        // Rapikan nomor urut
        rabItems.forEach((item, index) => {
            item.order_no = index + 1;
        });

        renderRabItems();

        renderFloorOptions();

        renderCategoryOptions();

        calculateSummary();

        const modalElement =
            document.getElementById('importRabItemModal');

        const modal =
            bootstrap.Modal.getInstance(modalElement);

        if (modal) {
            modal.hide();
        }

        importedRabItems = [];
    }

    function parseRupiah(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        let str = String(value)
            .trim()
            .replace(/Rp/gi, '')
            .replace(/\s/g, '');

    
        if (str.includes(',')) {
            str = str.replace(/\./g, '');
            str = str.replace(',', '.');
        }

        return parseFloat(str) || 0;
    }
    function formatRupiah(value) {

        value = Number(value) || 0;

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    function parseDecimal(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        let str = String(value)
            .trim()
            .replace(/\s/g, '');

        if (str.includes(',')) {
            str = str.replace(/\./g, '');
            str = str.replace(',', '.');
        }

        return parseFloat(str) || 0;
    }
    function initRabSelect2() {

        const floorSelect = $('#rab_item_floor');

        if (floorSelect.hasClass('select2-hidden-accessible')) {
            floorSelect.select2('destroy');
        }

        floorSelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#addRabItemModal'),
            placeholder: '-- Pilih Lantai --',
            allowClear: true
        });


        const categorySelect = $('#rab_item_category');

        if (categorySelect.hasClass('select2-hidden-accessible')) {
            categorySelect.select2('destroy');
        }

        categorySelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#addRabItemModal'),
            placeholder: '-- Pilih Kategori --',
            allowClear: true
        });

    }
    function getRabFloors() {

        return [
            ...new Set(
                rabItems
                    .map(item => item.floor_name)
                    .filter(value => value && value.trim() !== '')
            )
        ];
    }

    function getRabCategories(floor = null) {

        let items = rabItems;

        if (floor) {

            items = items.filter(
                item => item.floor_name === floor
            );

        }

        return [
            ...new Set(
                items
                    .map(item => item.category_name)
                    .filter(value => value && value.trim() !== '')
            )
        ];
    }
    function renderFloorOptions(selectedValue = '') {

        const select =
            document.getElementById('rab_item_floor');

        if (!select) return;

        const floors = getRabFloors();

        select.innerHTML = `
            <option value="">
                -- Pilih Lantai --
            </option>
        `;

        // Lantai yang sudah pernah digunakan
        floors.forEach(floor => {

            const option =
                document.createElement('option');

            option.value = floor;
            option.textContent = floor;

            select.appendChild(option);

        });

        // Selalu tampilkan pilihan tambah lantai
        const newOption =
            document.createElement('option');

        newOption.value = '__new__';
        newOption.textContent = '+ Tambah Lantai Baru';

        select.appendChild(newOption);


        if (selectedValue) {

            select.value = selectedValue;

        }

    }
    function renderCategoryOptions(floor = null) {

        const select =
            document.getElementById('rab_item_category');

        if (!select) return;

        const categories =
            getRabCategories(floor);

        select.innerHTML = `
            <option value="">
                -- Pilih Kategori --
            </option>
        `;

        categories.forEach(category => {

            const option =
                document.createElement('option');

            option.value = category;
            option.textContent = category;

            select.appendChild(option);

        });

        const newOption =
            document.createElement('option');

        newOption.value = '__new__';
        newOption.textContent =
            '+ Tambah Kategori Baru';

        select.appendChild(newOption);
    }
    function handleFloorChange() {

        const select =
            document.getElementById('rab_item_floor');

        const value = select.value;

        if (value === '__new__') {

            showNewFloorInput();

            return;

        }

        renderCategoryOptions(value);

    }
    function showNewFloorInput() {

        document.getElementById('floorSelectWrapper').classList.add('d-none');

        document
            .getElementById('floorInputWrapper')
            .classList.remove('d-none');

        document
            .getElementById('rab_item_floor_new')
            .value = '';

        document
            .getElementById('rab_item_floor_new')
            .focus();

    }
    function cancelNewFloor() {

        document
            .getElementById('floorInputWrapper')
            .classList.add('d-none');

        document
            .getElementById('floorSelectWrapper')
            .classList.remove('d-none');

        renderFloorOptions();

    }
    function showNewCategoryInput() {

        document
            .getElementById('categorySelectWrapper')
            .classList.add('d-none');

        document
            .getElementById('categoryInputWrapper')
            .classList.remove('d-none');

        document
            .getElementById('rab_item_category_new')
            .value = '';

        document
            .getElementById('rab_item_category_new')
            .focus();

    }
    function cancelNewCategory() {

        document
            .getElementById('categoryInputWrapper')
            .classList.add('d-none');

        document
            .getElementById('categorySelectWrapper')
            .classList.remove('d-none');

        const floor =
            document.getElementById('rab_item_floor').value;

        renderCategoryOptions(floor);

    }
    function getSelectedFloor() {

        const select =
            document.getElementById('rab_item_floor');

        const newInput =
            document.getElementById('rab_item_floor_new');

        if (
            !document
                .getElementById('floorInputWrapper')
                .classList.contains('d-none')
        ) {

            return newInput.value.trim();

        }

        return select.value.trim();
    }
    function getSelectedCategory() {

        const select =
            document.getElementById('rab_item_category');

        const newInput =
            document.getElementById('rab_item_category_new');

        if (
            !document
                .getElementById('categoryInputWrapper')
                .classList.contains('d-none')
        ) {

            return newInput.value.trim();

        }

        return select.value.trim();
    }
    function openAddRabItemModal() {

        if (rabDescriptionEditor) {
            rabDescriptionEditor.setContents([]);
        }

        document.getElementById('rab_item_description').value = '';
        document.getElementById('rab_item_volume').value = '';

        const price = document.getElementById('rab_item_price_display');

        price.value = '';
        price.dataset.value = 0;

        document.getElementById('rab_item_price').value = '';

        const modal = new bootstrap.Modal(
            document.getElementById('addRabItemModal')
        );

        modal.show();
    }

    function saveRabItem() {

        const description = rabDescriptionEditor
            ? rabDescriptionEditor.root.innerHTML.trim()
            : '';

        const volumeInput =
            document.getElementById('rab_item_volume').value;

        const volume =
            parseDecimal(volumeInput);

        const basePrice =
            parseRupiah(
                document.getElementById('rab_item_price_display').value
            );


        if (volume <= 0) {
            alert('Qty harus lebih besar dari 0.');
            document.getElementById('rab_item_volume').focus();
            return;
        }

        if (basePrice < 0) {
            alert('Harga tidak valid.');
            document.getElementById('rab_item_price_display').focus();
            return;
        }


        const price = calculateItemPrice(basePrice);

        const total = volume * price;


        rabItems.push({
            temp_id: 'item_' + (++itemCounter),
            description: description,
            volume: volume,
            base_price: basePrice,
            price: price,
            total: total,
            order_no: rabItems.length + 1
        });


        renderRabItems();

        calculateSummary();


        const modalElement =
            document.getElementById('addRabItemModal');

        const modal =
            bootstrap.Modal.getInstance(modalElement);

        if (modal) {
            modal.hide();
        }
    }
    function calculateItemPrice(basePrice) {

        basePrice = Number(basePrice) || 0;

        const profit =
            Number(
                document.getElementById('rab_profit_display')?.value
            ) || 0;

        const overhead =
            Number(
                document.getElementById('rab_overhead_display')?.value
            ) || 0;

        const overheadAmount =
            basePrice * overhead / 100;

        const profitAmount =
            basePrice * profit / 100;

        return basePrice
            + overheadAmount
            + profitAmount;
    }
    function recalculateAllItems() {

        rabItems.forEach(item => {

            const basePrice =
                Number(item.base_price) || 0;

            item.price =
                calculateItemPrice(basePrice);

            item.total =
                Number(item.volume || 0) *
                item.price;

        });

        renderRabItems();

        calculateSummary();
    }
function renderRabItems() {

    const tbody = document.getElementById('rab_offerItemsBody');

    if (!tbody) return;

    tbody.innerHTML = '';

    if (rabItems.length === 0) {

        tbody.innerHTML = `
            <tr class="empty-rab-row">
                <td colspan="6"
                    class="text-center text-muted py-5">

                    Belum ada item RAB.

                </td>
            </tr>
        `;

        return;
    }

    rabItems.forEach((item, index) => {

        const description = item.description || '';

        tbody.insertAdjacentHTML(
            'beforeend',
            `
            <tr class="job-row"
                id="${item.temp_id}"
                data-id="${item.temp_id}">

                <td class="text-center">
                    ${index + 1}
                </td>

                <td>

                    <div class="rab-description-preview">

                        ${
                            description
                                ? description
                                : '<span class="text-muted">-</span>'
                        }

                    </div>

                </td>

                <td>

                    <input type="number"
                           class="form-control vol"
                           value="${item.volume ?? 0}"
                           min="0"
                           step="any"
                           onchange="updateItemVolume(
                               '${item.temp_id}',
                               this.value
                           )">

                </td>

                <td>

                    <input type="text"
                           class="form-control harga"
                           value="${formatRupiah(item.base_price)}"
                           onchange="updateItemPrice(
                               '${item.temp_id}',
                               this
                           )">

                </td>

                <td>

                    <input type="text"
                           class="form-control total"
                           value="${formatRupiah(item.total)}"
                           readonly>

                </td>

                <td class="text-center">

                    <button type="button"
                            class="btn btn-sm btn-danger"
                            onclick="removeRabItem(
                                '${item.temp_id}'
                            )">

                        −

                    </button>

                </td>

            </tr>
            `
        );

    });
    updateSortable();
}

function updateItemVolume(id, value) {

    const item = rabItems.find(
        item => item.temp_id === id
    );

    if (!item) return;

    item.volume = parseDecimal(value);

    item.total =
        Number(item.volume || 0) *
        Number(item.price || 0);

    renderRabItems();
    calculateSummary();
}

    function updateItemDay(id, value) {
        const item =
            rabItems.find(item =>
                item.temp_id === id
            );
        if (!item) return;
        item.day = parseInt(value, 10) || 1;
        renderRabItems();
        calculateSummary();
    }

    function updateItemPrice(id, element) {

        const item = rabItems.find(
            item => item.temp_id === id
        );

        if (!item) return;

        const basePrice =
            parseRupiah(element.value);

        item.base_price = basePrice;

        item.price =
            calculateItemPrice(basePrice);

        item.total =
            Number(item.volume || 0) *
            Number(item.price || 0);

        renderRabItems();
        calculateSummary();
    }

    function removeRabItem(id) {
        const index =
            rabItems.findIndex(item =>
                item.temp_id === id
            );
        if (index === -1) return;
        rabItems.splice(index, 1);
        normalizeOrder();
        renderRabItems();
        calculateSummary();
    }

    function removeCategory(floorName, categoryName) {

        if (!confirm(
            `Hapus seluruh pekerjaan kategori "${categoryName}"?`
        )) {
            return;
        }

        rabItems = rabItems.filter(item => {

            return !(
                item.floor_name === floorName &&
                item.category_name === categoryName
            );

        });

        normalizeOrder();

        renderRabItems();

        calculateSummary();
    }

    function normalizeOrder() {

        rabItems.forEach((item, index) => {

            item.order_no = index + 1;

        });

    }

    function numberToLetters(num) {

        let letters = '';

        num = num + 1;

        while (num > 0) {

            const rem =
                (num - 1) % 26;

            letters =
                String.fromCharCode(
                    65 + rem
                ) + letters;

            num =
                Math.floor(
                    (num - 1) / 26
                );
        }

        return letters;
    }

    function calculateSummary() {

        const subtotal = rabItems.reduce(
            (sum, item) => {

                return sum +
                    (Number(item.total) || 0);

            },
            0
        );

        const discount =
            parseRupiah(
                document.getElementById('rab_discount_display')?.value
            ) || 0;


        const subtotalAfterDiscount =
            Math.max(
                0,
                subtotal - discount
            );

        const taxRate =
            parseFloat(
                document.getElementById('rab_tax_rate')?.value
            ) || 0;


        const taxTotal =
            subtotalAfterDiscount *
            taxRate /
            100;

        const shipping =
            parseRupiah(
                document.getElementById('rab_shipping_display')?.value
            ) || 0;

        const grandTotal =
            subtotalAfterDiscount +
            taxTotal +
            shipping;

        document.getElementById('rab_subtotalDisplay').textContent = formatRupiah(subtotal);

        document.getElementById('rab_subAfterDiscountDisplay').textContent = formatRupiah(subtotalAfterDiscount);

        document.getElementById('rab_totalTaxDisplay').textContent = formatRupiah(taxTotal);

        document.getElementById('rab_grandTotalDisplay').textContent = formatRupiah(grandTotal);

        document.getElementById('rab_subtotal').value = subtotal;

        document.getElementById('rab_discount').value = discount;

        document.getElementById('rab_subAfterDiscount').value = subtotalAfterDiscount;

        document.getElementById('rab_tax_total').value = taxTotal;

        document.getElementById('rab_shipping').value = shipping;

        document.getElementById('rab_grand_total').value = grandTotal;
    }

    function initRupiahInputs() {

        const discountInput = document.getElementById('rab_discount_display');

        if (discountInput) {

            discountInput.addEventListener('input', function () {

                const value = parseRupiah(this.value);

                document.getElementById('rab_discount').value = value;

                calculateSummary();
            });

            discountInput.addEventListener('blur', function () {

                const value = parseRupiah(this.value);

                this.value = value > 0
                    ? formatRupiah(value)
                    : '';

                document.getElementById('rab_discount').value = value;
            });
        }

        const shippingInput = document.getElementById('rab_shipping_display');

        if (shippingInput) {

            shippingInput.addEventListener('input', function () {

                const value = parseRupiah(this.value);

                document.getElementById('rab_shipping').value = value;

                calculateSummary();
            });

            shippingInput.addEventListener('blur', function () {

                const value = parseRupiah(this.value);

                this.value = value > 0
                    ? formatRupiah(value)
                    : '';

                document.getElementById('rab_shipping').value = value;
            });
        }

        const price = document.getElementById('rab_item_price_display');

        if (price) {
            price.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9.,]/g, '');
            });

            price.addEventListener('blur', function () {
                const value = parseRupiah(this.value);
                this.dataset.value = value;
                this.value = formatRupiah(value);
            });
        }
    }

    function updateSortable() {

        if (sortableInstance) {

            sortableInstance.destroy();

            sortableInstance = null;

        }


        if (currentMode !== 'drag') {
            return;
        }

        const tbody = document.getElementById(
                'rab_offerItemsBody'
            );

        if (!tbody) return;


        sortableInstance =
            new Sortable(tbody, {

                animation: 150,

                handle: '.drag-handle',

                draggable: '.job-row',

                onEnd: function () {

                    const rows =
                        tbody.querySelectorAll(
                            '.job-row'
                        );


                    const newOrder = [];


                    rows.forEach(row => {

                        const item =
                            rabItems.find(
                                item =>
                                    item.temp_id ===
                                    row.dataset.id
                            );

                        if (item) {

                            newOrder.push(item);

                        }

                    });

                    rabItems = newOrder;

                    normalizeOrder();

                    renderRabItems();

                }

            });

    }

    function setModeCreate(mode) {

        currentMode = mode;


        const btnEdit =
            document.getElementById(
                'tombolUbah'
            );

        const btnDrag =
            document.getElementById(
                'tombolGeser'
            );


        if (btnEdit) {

            btnEdit.classList.toggle(
                'btn-dark',
                mode === 'edit'
            );

            btnEdit.classList.toggle(
                'btn-outline-secondary',
                mode !== 'edit'
            );

        }


        if (btnDrag) {

            btnDrag.classList.toggle(
                'btn-dark',
                mode === 'drag'
            );

            btnDrag.classList.toggle(
                'btn-outline-secondary',
                mode !== 'drag'
            );

        }


        if (mode === 'drag') {

            document.body.classList.add(
                'drag-mode'
            );

        } else {

            document.body.classList.remove(
                'drag-mode'
            );

        }
        updateSortable();
    }

    function escapeHtml(value) {

        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

                const div =
        document.createElement('div');

    div.textContent =
        value ?? '';

    return div.innerHTML;

    }

    function escapeAttribute(value) {

        return String(value || '')
            .replace(/\\/g, '\\\\')
            .replace(/'/g, "\\'");

    }

    function prepareRabItemsForSubmit() {
        const profitDisplay = document.getElementById('rab_profit_display');

        const overheadDisplay = document.getElementById('rab_overhead_display');

        document.getElementById('rab_profit').value = Number(profitDisplay?.value || 0);

        document.getElementById('rab_overhead').value = Number(overheadDisplay?.value || 0);
        const container =
            document.getElementById(
                'rabItemsContainer'
            );

        if (!container) return;


        container.innerHTML = '';


        rabItems.forEach(
            (item, index) => {
                const fields = {
                    description: item.description || '',
                    volume: item.volume,
                    base_price: item.base_price,
                    price: item.price,
                    total: item.total,
                    order_no: index + 1
                };

                Object.entries(fields)
                    .forEach(
                        ([key, value]) => {

                            const input =
                                document.createElement(
                                    'input'
                                );

                            input.type =
                                'hidden';

                            input.name =
                                `items[${index}][${key}]`;

                            input.value =
                                value ?? '';

                            container.appendChild(
                                input
                            );

                        }
                    );

            }
        );

    }

    document.addEventListener('DOMContentLoaded', function () {
        initRupiahInputs();
        calculateSummary();
        const profitInput = document.getElementById('rab_profit_display');

        if (profitInput) {

            profitInput.addEventListener(
                'input',
                recalculateAllItems
            );

        }

        const overheadInput = document.getElementById('rab_overhead_display');
        if (overheadInput) {
            overheadInput.addEventListener(
                'input',
                recalculateAllItems
            );
        }

        const discountInput = document.getElementById('rab_discount_display');
        if (discountInput) {

            discountInput.addEventListener(
                'input',
                calculateSummary
            );

        }

        const taxInput = document.getElementById('rab_tax_rate');

        if (taxInput) {

            taxInput.addEventListener(
                'input',
                calculateSummary
            );

        }

        const shippingInput = document.getElementById('rab_shipping_display');

        if (shippingInput) {

            shippingInput.addEventListener(
                'input',
                calculateSummary
            );

        }

        const editButton = document.getElementById('tombolUbah');

        if (editButton) {

            editButton.addEventListener(
                'click',
                function () {

                    setModeCreate('edit');

                }
            );

        }

        const dragButton = document.getElementById('tombolGeser');

        if (dragButton) {

            dragButton.addEventListener(
                'click',
                function () {

                    setModeCreate('drag');

                }
            );

        }

const form = document.getElementById('rabForm');

if (form) {
    form.addEventListener('submit', function () {

        calculateSummary();
        prepareRabItemsForSubmit();

        console.log(
            Object.fromEntries(
                new FormData(form)
            )
        );
    });
}
        const floorSelect = document.getElementById('rab_item_floor');

        if (floorSelect) {

            floorSelect.addEventListener(
                'change',
                handleFloorChange
            );

        }

        const categorySelect = document.getElementById('rab_item_category');

        if (categorySelect) {
            categorySelect.addEventListener(
                'change',
                function () {

                    if (this.value === '__new__') {

                        showNewCategoryInput();

                    }

                }
            );

        }
        renderRabItems();

    });
</script>
@endpush