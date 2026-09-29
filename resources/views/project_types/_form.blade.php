{{-- resources/views/project-types/_form.blade.php --}}
{{-- $projectType, $levels sudah disiapkan controller --}}

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Nama Jenis Proyek</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $projectType->name) }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Kode (unik, huruf/angka/strip)</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $projectType->code) }}" placeholder="mis. wedding, event" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
              rows="2">{{ old('description', $projectType->description) }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3 form-check">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
           {{ old('is_active', $projectType->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Aktif</label>
</div>

<hr>

<h6 class="mb-2">Step / Tahapan Proyek</h6>
<p class="text-muted small">
    Urutan step diambil dari urutan baris di bawah ini (bisa ubah pakai tombol naik/turun).
</p>

<div id="levels-wrapper">
    @foreach($levels as $i => $level)
        <div class="row mb-2 level-row align-items-center">
            <div class="col-auto step-number fw-bold" style="width:40px">{{ $i + 1 }}.</div>
            <div class="col">
                <input type="text" name="levels[{{ $i }}][level_name]" class="form-control"
                       value="{{ old("levels.$i.level_name", $level['level_name'] ?? '') }}"
                       placeholder="Nama step, mis. Penawaran Harga" required>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-outline-secondary move-up">&uarr;</button>
                <button type="button" class="btn btn-sm btn-outline-secondary move-down">&darr;</button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button>
            </div>
        </div>
    @endforeach
</div>

<button type="button" id="add-level" class="btn btn-sm btn-success mt-1">+ Tambah Step</button>

                    <div class="text-end mt-5">
                        <button type="submit" class="btn btn-dark px-4">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Data
                        </button>
                    </div>

@push('js')
<script>
(function () {
    const wrapper = document.getElementById('levels-wrapper');
    const addBtn  = document.getElementById('add-level');

    function renumber() {
        wrapper.querySelectorAll('.level-row').forEach((row, index) => {
            row.querySelector('.step-number').textContent = (index + 1) + '.';
            row.querySelector('input[type="text"]').setAttribute('name', `levels[${index}][level_name]`);
        });
    }

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row mb-2 level-row align-items-center';
        row.innerHTML = `
            <div class="col-auto step-number fw-bold" style="width:40px"></div>
            <div class="col">
                <input type="text" class="form-control" placeholder="Nama step" required>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-outline-secondary move-up">&uarr;</button>
                <button type="button" class="btn btn-sm btn-outline-secondary move-down">&darr;</button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button>
            </div>`;
        wrapper.appendChild(row);
        renumber();
    });

    wrapper.addEventListener('click', function (e) {
        const row = e.target.closest('.level-row');
        if (!row) return;

        if (e.target.classList.contains('remove-row')) {
            if (wrapper.querySelectorAll('.level-row').length <= 1) {
                alert('Minimal harus ada 1 step.');
                return;
            }
            row.remove();
            renumber();
        }

        if (e.target.classList.contains('move-up') && row.previousElementSibling) {
            wrapper.insertBefore(row, row.previousElementSibling);
            renumber();
        }

        if (e.target.classList.contains('move-down') && row.nextElementSibling) {
            wrapper.insertBefore(row.nextElementSibling, row);
            renumber();
        }
    });
})();
</script>
@endpush