@extends('layouts.app')

@section('title', 'Tambah Jenis Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Tambah Jenis Seminar Baru</h1>

        <form action="{{ route('admin.seminarjenis.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Seminar</label>
                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nama') border-red-500 @enderror"
                        placeholder="Contoh: Seminar Usul, Seminar Hasil, Ujian Skripsi"
                        required
                    >
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">Kode Jenis (tanpa spasi)</label>
                    <input
                        type="text"
                        name="kode"
                        id="kode"
                        value="{{ old('kode') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('kode') border-red-500 @enderror"
                        placeholder="Contoh: SUSUL, SHAS, UKRP"
                        required
                    >
                    @error('kode')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Opsional)</label>
                    <textarea
                        name="keterangan"
                        id="keterangan"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('keterangan') border-red-500 @enderror"
                        placeholder="Deskripsi tambahan tentang jenis seminar ini">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Tim Evaluator</h3>
                    <p class="text-xs text-gray-600 mb-3">Centang evaluator yang <span class="font-semibold">wajib</span> mengisi nilai & tanda tangan untuk menyelesaikan seminar.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-lg border border-gray-200 p-3">
                            <input type="hidden" name="p1_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                <input type="checkbox" name="p1_required" value="1" {{ old('p1_required', 1) ? 'checked' : '' }}>
                                <span>Pembimbing 1</span>
                            </label>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-3">
                            <input type="hidden" name="p2_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                <input type="checkbox" name="p2_required" value="1" {{ old('p2_required', 1) ? 'checked' : '' }}>
                                <span>Pembimbing 2</span>
                            </label>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-3">
                            <input type="hidden" name="pembahas_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                <input type="checkbox" name="pembahas_required" value="1" {{ old('pembahas_required', 1) ? 'checked' : '' }}>
                                <span>Pembahas</span>
                            </label>
                        </div>
                    </div>

                    @error('p1_required')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                @if(($syaratReady ?? false) === false)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Fitur syarat seminar belum aktif (kolom database belum ada). Jalankan: <span class="font-mono">php artisan migrate</span>
                    </div>
                @else
                    <div>
                        <label for="syarat_seminar" class="block text-sm font-medium text-gray-700 mb-1">Syarat Seminar (Opsional)</label>
                        <textarea
                            name="syarat_seminar"
                            id="syarat_seminar"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('syarat_seminar') border-red-500 @enderror"
                            placeholder="Tuliskan syarat seminar yang harus dipenuhi mahasiswa (akan tampil sebelum upload berkas syarat)">{{ old('syarat_seminar') }}</textarea>
                        @error('syarat_seminar')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if(($berkasItemsReady ?? false) === false)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Fitur upload syarat belum aktif (kolom database belum ada). Jalankan: <span class="font-mono">php artisan migrate</span>
                    </div>
                @endif

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-file-list text-blue-500"></i>
                                Konfigurasi Berkas Persyaratan
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Daftar berkas yang wajib diunggah mahasiswa saat pengajuan.</p>
                        </div>
                        <button type="button" id="add-berkas-item" class="btn-pill btn-pill-primary text-xs px-4 py-2">
                            <i class="fas fa-plus mr-1"></i> Tambah Syarat
                        </button>
                    </div>

                    <div class="hidden md:grid md:grid-cols-12 gap-4 px-2 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        <div class="md:col-span-5">Nama Dokumen / Syarat</div>
                        <div class="md:col-span-3">Jenis File</div>
                        <div class="md:col-span-2 text-center">Maks (MB)</div>
                        <div class="md:col-span-1 text-center">Wajib</div>
                        <div class="md:col-span-1"></div>
                    </div>

                    @if(($berkasItemsReady ?? false) === false)
                        <div class="text-sm text-gray-600">(Tidak tersedia sebelum migrasi dijalankan)</div>
                    @else
                        <div id="berkas-items" class="space-y-3 mt-2"></div>
                    @endif
                </div>

                <!-- Evaluator Weight Percentages -->
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Bobot Persentase Penilai</h3>
                    <p class="text-sm text-gray-600 mb-4">Tentukan bobot persentase untuk setiap penilai. Total harus 100%.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="p1_weight" class="block text-sm font-medium text-blue-700 mb-1">Pembimbing 1 (P1) %</label>
                            <input
                                type="number"
                                name="p1_weight"
                                id="p1_weight"
                                value="{{ old('p1_weight', 35) }}"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full px-3 py-2 border-2 border-blue-300 rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-200 @error('p1_weight') border-red-500 @enderror"
                                placeholder="35"
                                required
                            >
                            @error('p1_weight')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="p2_weight" class="block text-sm font-medium text-green-700 mb-1">Pembimbing 2 (P2) %</label>
                            <input
                                type="number"
                                name="p2_weight"
                                id="p2_weight"
                                value="{{ old('p2_weight', 35) }}"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full px-3 py-2 border-2 border-green-300 rounded-md focus:border-green-500 focus:ring-2 focus:ring-green-200 @error('p2_weight') border-red-500 @enderror"
                                placeholder="35"
                                required
                            >
                            @error('p2_weight')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pembahas_weight" class="block text-sm font-medium text-purple-700 mb-1">Pembahas (PMB) %</label>
                            <input
                                type="number"
                                name="pembahas_weight"
                                id="pembahas_weight"
                                value="{{ old('pembahas_weight', 30) }}"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full px-3 py-2 border-2 border-purple-300 rounded-md focus:border-purple-500 focus:ring-2 focus:ring-purple-200 @error('pembahas_weight') border-red-500 @enderror"
                                placeholder="30"
                                required
                            >
                            @error('pembahas_weight')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-white rounded-lg border border-indigo-300">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total Bobot:</span>
                            <span id="total-weight" class="text-lg font-bold text-indigo-600">100%</span>
                        </div>
                        <p id="weight-warning" class="text-xs text-red-600 mt-1 hidden">Total harus 100%</p>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.seminarjenis.index') }}" class="btn-pill btn-pill-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Simpan Jenis Seminar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function() {
    function initSeminarJenisCreate() {
        const p1Weight = document.getElementById('p1_weight');
        const p2Weight = document.getElementById('p2_weight');
        const pembahasWeight = document.getElementById('pembahas_weight');
        const addBerkasBtn = document.getElementById('add-berkas-item');
        const container = document.getElementById('berkas-items');

        if (!p1Weight || !p2Weight || !pembahasWeight) return;
        if (p1Weight.dataset.initialized === 'true') return;

        // Weight Logic
        function updateTotalWeight() {
            const v1 = parseFloat(p1Weight.value) || 0;
            const v2 = parseFloat(p2Weight.value) || 0;
            const vm = parseFloat(pembahasWeight.value) || 0;
            const total = v1 + v2 + vm;

            const totalEl = document.getElementById('total-weight');
            const warnEl = document.getElementById('weight-warning');
            if (totalEl) {
                totalEl.textContent = total.toFixed(2) + '%';
                if (Math.abs(total - 100) < 0.01) {
                    totalEl.classList.remove('text-red-600');
                    totalEl.classList.add('text-green-600');
                    warnEl?.classList.add('hidden');
                } else {
                    totalEl.classList.remove('text-green-600');
                    totalEl.classList.add('text-red-600');
                    warnEl?.classList.remove('hidden');
                }
            }
        }

        p1Weight.addEventListener('input', updateTotalWeight);
        p2Weight.addEventListener('input', updateTotalWeight);
        pembahasWeight.addEventListener('input', updateTotalWeight);
        updateTotalWeight();

        // Berkas Rows Logic
        let berkasKeyCounter = 0;
        function addBerkasRow(keyValue = '', labelValue = '', extValue = '', maxMbValue = '', requiredChecked = true) {
            if (!container) return;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-12 gap-3 bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:border-blue-200 transition-all group';
            if (!keyValue) {
                berkasKeyCounter += 1;
                keyValue = `item_${Date.now()}_${berkasKeyCounter}`;
            }
            row.innerHTML = `
                <div class="md:col-span-5">
                    <label class="block md:hidden text-[10px] font-bold text-gray-400 uppercase mb-1">Judul Syarat</label>
                    <input type="text" name="berkas_items_label[]" value="${labelValue}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-blue-300 focus:ring-1 focus:ring-blue-100" placeholder="e.g. Scan Ijazah" required>
                    <input type="hidden" name="berkas_items_key[]" value="${keyValue}">
                </div>
                <div class="md:col-span-3">
                    <label class="block md:hidden text-[10px] font-bold text-gray-400 uppercase mb-1">Ekstensi (PDF, JPG, dll)</label>
                    <input type="text" name="berkas_items_extensions[]" value="${extValue}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-blue-300 focus:ring-1 focus:ring-blue-100" placeholder="pdf, jpg">
                </div>
                <div class="md:col-span-2">
                    <label class="block md:hidden text-[10px] font-bold text-gray-400 uppercase mb-1">Ukuran Maks (MB)</label>
                    <input type="number" name="berkas_items_max_size_mb[]" value="${maxMbValue}" min="0.1" max="50" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:border-blue-300 focus:ring-1 focus:ring-blue-100" placeholder="5">
                </div>
                <div class="md:col-span-1 flex items-center justify-center">
                    <label class="flex flex-col items-center">
                        <span class="md:hidden text-[10px] font-bold text-gray-400 uppercase mb-1">Wajib</span>
                        <input type="checkbox" name="berkas_items_required[]" value="1" ${requiredChecked ? 'checked' : ''} class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </label>
                </div>
                <div class="md:col-span-1 flex items-center justify-end">
                    <button type="button" class="text-gray-400 hover:text-red-500 transition-colors p-2" data-remove-row title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
            row.querySelector('[data-remove-row]').addEventListener('click', () => row.remove());
            container.appendChild(row);
        }

        if (addBerkasBtn) {
            addBerkasBtn.addEventListener('click', () => addBerkasRow('', '', '', '', true));
        }

        // Initial empty row if none
        if (container && container.children.length === 0) {
            addBerkasRow('', '', '', '', true);
        }

        p1Weight.dataset.initialized = 'true';
    }

    // Standardized Init Pattern
    if (document.readyState !== 'loading') {
        initSeminarJenisCreate();
    } else {
        document.addEventListener('DOMContentLoaded', initSeminarJenisCreate);
    }
    window.addEventListener('app:init', initSeminarJenisCreate);
    window.addEventListener('page-loaded', initSeminarJenisCreate);
})();
</script>
@endsection
