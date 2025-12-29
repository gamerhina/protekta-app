@extends('layouts.app')

@section('title', 'Edit Jenis Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Edit Jenis Seminar</h1>
            <div id="autoSaveIndicator" class="text-sm text-gray-500 hidden">
                <span class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="saveStatusText">Draft tersimpan otomatis</span>
                </span>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.seminarjenis.update', $seminarJenis->id) }}" method="POST" id="basicInfoForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_type" value="basic_info">
            <div class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Seminar</label>
                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama', $seminarJenis->nama) }}"
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
                        value="{{ old('kode', $seminarJenis->kode) }}"
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
                        placeholder="Deskripsi tambahan tentang jenis seminar ini">{{ old('keterangan', $seminarJenis->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-slate-800 mb-2">Tim Evaluator</h3>
                    <p class="text-xs text-slate-600 mb-3">Centang evaluator yang <span class="font-semibold">wajib</span> mengisi nilai & tanda tangan untuk menyelesaikan seminar.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-lg border border-slate-200 p-3">
                            <input type="hidden" name="p1_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                                <input type="checkbox" name="p1_required" value="1" {{ old('p1_required', $seminarJenis->p1_required ?? true) ? 'checked' : '' }}>
                                <span>Pembimbing 1</span>
                            </label>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-3">
                            <input type="hidden" name="p2_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                                <input type="checkbox" name="p2_required" value="1" {{ old('p2_required', $seminarJenis->p2_required ?? true) ? 'checked' : '' }}>
                                <span>Pembimbing 2</span>
                            </label>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-3">
                            <input type="hidden" name="pembahas_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                                <input type="checkbox" name="pembahas_required" value="1" {{ old('pembahas_required', $seminarJenis->pembahas_required ?? true) ? 'checked' : '' }}>
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
                            placeholder="Tuliskan syarat seminar yang harus dipenuhi mahasiswa (akan tampil sebelum upload berkas syarat)">{{ old('syarat_seminar', $seminarJenis->syarat_seminar) }}</textarea>
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

                        @php
                            $existingItems = old('berkas_items_label') !== null
                                ? null
                                : ($seminarJenis->berkas_syarat_items ?? []);
                        @endphp

                        @if(is_array($existingItems) && count($existingItems))
                            <script type="application/json" id="existing-berkas-items">
                                {!! json_encode($existingItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                            </script>
                        @endif
                    @endif
                </div>

                <!-- Hidden fields for weights -->
                <input type="hidden" name="p1_weight" id="basic_p1_weight" value="{{ $seminarJenis->p1_weight ?? 35 }}">
                <input type="hidden" name="p2_weight" id="basic_p2_weight" value="{{ $seminarJenis->p2_weight ?? 35 }}">
                <input type="hidden" name="pembahas_weight" id="basic_pembahas_weight" value="{{ $seminarJenis->pembahas_weight ?? 30 }}">
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-end pt-6 border-t border-gray-200">
                <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    Simpan Informasi Dasar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Aspect Management Section (Outside form to allow nested forms) -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Kelola Aspek Penilaian</h2>
            <p class="text-sm text-gray-600 mb-6">Konfigurasikan aspek-aspek penilaian untuk jenis seminar ini. Setiap penilai (P1, P2, Pembahas) dapat memiliki aspek penilaian yang berbeda.</p>

            <!-- Add New Aspect Form -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-800 mb-3">Tambah Aspek Penilaian Baru</h3>
                <form action="{{ route('admin.seminarjenis.aspects.store', $seminarJenis) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <label for="evaluator_type" class="block text-sm font-medium text-gray-700 mb-1">Penilai</label>
                        <select name="evaluator_type" id="evaluator_type" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                            <option value="">Pilih Penilai</option>
                            <option value="p1">Pembimbing 1 (P1)</option>
                            <option value="p2">Pembimbing 2 (P2)</option>
                            <option value="pembahas">Pembahas (PMB)</option>
                        </select>
                    </div>
                    <div>
                        <label for="nama_aspek" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                        <input type="text" name="nama_aspek" id="nama_aspek" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Nilai Penguasaan Materi" required>
                    </div>
                    <div>
                        <label for="urutan" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <input type="number" name="urutan" id="urutan" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="1" required>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full btn-pill btn-pill-primary text-sm">
                            Tambah
                        </button>
                    </div>
                </form>
            </div>

            <!-- Display Existing Aspects -->
            @foreach(['p1' => 'Pembimbing 1 (P1)', 'p2' => 'Pembimbing 2 (P2)', 'pembahas' => 'Pembahas (PMB)'] as $type => $label)
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $label }}</h3>
                        @if(isset($aspects[$type]) && $aspects[$type]->count() > 0)
                            <span class="px-3 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $aspects[$type]->count() }} Aspek
                            </span>
                        @endif
                    </div>

                    @if(isset($aspects[$type]) && $aspects[$type]->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Urutan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Aspek</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($aspects[$type] as $aspect)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm">{{ $aspect->urutan }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $aspect->nama_aspek }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex space-x-2">
                                                <button onclick="editAspect({{ $aspect->id }}, '{{ addslashes($aspect->nama_aspek) }}', {{ $aspect->urutan }})" class="text-blue-600 hover:text-blue-900 font-medium">
                                                    Edit
                                                </button>
                                                <form action="{{ route('admin.seminarjenis.aspects.destroy', [$seminarJenis, $aspect]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aspek ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center border-2 border-dashed border-gray-300">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Belum ada aspek penilaian untuk {{ $label }}</p>
                            <p class="text-xs text-gray-400 mt-1">Gunakan form di atas untuk menambah aspek</p>
                        </div>
                    @endif
                </div>
            @endforeach
    </div>
</div>

<!-- Evaluator Weight Percentages & Submit Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <form action="{{ route('admin.seminarjenis.update', $seminarJenis->id) }}" method="POST" id="weightsForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_type" value="weights">
            <input type="hidden" name="nama" id="weight_nama" value="{{ $seminarJenis->nama }}">
            <input type="hidden" name="kode" id="weight_kode" value="{{ $seminarJenis->kode }}">
            <input type="hidden" name="keterangan" id="weight_keterangan" value="{{ $seminarJenis->keterangan }}">
            <input type="hidden" name="syarat_seminar" id="weight_syarat_seminar" value="{{ $seminarJenis->syarat_seminar }}">
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Bobot Persentase Penilai</h3>
                <p class="text-sm text-gray-600 mb-4">Tentukan bobot persentase untuk setiap penilai. Total harus 100%.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="p1_weight_bottom" class="block text-sm font-medium text-blue-700 mb-1">Pembimbing 1 (P1) %</label>
                        <input
                            type="number"
                            name="p1_weight"
                            id="p1_weight_bottom"
                            value="{{ old('p1_weight', $seminarJenis->p1_weight ?? 35) }}"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full px-3 py-2 border-2 border-blue-300 rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="35"
                            required
                        >
                    </div>

                    <div>
                        <label for="p2_weight_bottom" class="block text-sm font-medium text-green-700 mb-1">Pembimbing 2 (P2) %</label>
                        <input
                            type="number"
                            name="p2_weight"
                            id="p2_weight_bottom"
                            value="{{ old('p2_weight', $seminarJenis->p2_weight ?? 35) }}"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full px-3 py-2 border-2 border-green-300 rounded-md focus:border-green-500 focus:ring-2 focus:ring-green-200"
                            placeholder="35"
                            required
                        >
                    </div>

                    <div>
                        <label for="pembahas_weight_bottom" class="block text-sm font-medium text-purple-700 mb-1">Pembahas (PMB) %</label>
                        <input
                            type="number"
                            name="pembahas_weight"
                            id="pembahas_weight_bottom"
                            value="{{ old('pembahas_weight', $seminarJenis->pembahas_weight ?? 30) }}"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full px-3 py-2 border-2 border-purple-300 rounded-md focus:border-purple-500 focus:ring-2 focus:ring-purple-200"
                            placeholder="30"
                            required
                        >
                    </div>
                </div>

                <div class="mt-4 p-3 bg-white rounded-lg border border-indigo-300">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Total Bobot:</span>
                        <span id="total-weight-bottom" class="text-lg font-bold text-indigo-600">100%</span>
                    </div>
                    <p id="weight-warning-bottom" class="text-xs text-red-600 mt-1 hidden">Total harus 100%</p>
                </div>
            </div>

            <!-- Grading Scheme Section -->
            <div class="bg-gradient-to-r from-green-50 to-teal-50 border-2 border-green-200 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Skema Penilaian Huruf</h3>
                <p class="text-sm text-gray-600 mb-4">Tentukan batas nilai untuk setiap huruf mutu. Sistem akan melakukan pembulatan otomatis (contoh: 75.5 → 76 = A, 75.4 → 75 = B+)</p>

                <div class="space-y-3" id="grading-scheme-container">
                    @php
                        $defaultScheme = [
                            ['min' => 76, 'max' => 100, 'grade' => 'A'],
                            ['min' => 71, 'max' => 75.99, 'grade' => 'B+'],
                            ['min' => 66, 'max' => 70.99, 'grade' => 'B'],
                            ['min' => 61, 'max' => 65.99, 'grade' => 'C+'],
                            ['min' => 56, 'max' => 60.99, 'grade' => 'C'],
                            ['min' => 50, 'max' => 55.99, 'grade' => 'D'],
                            ['min' => 0, 'max' => 49.99, 'grade' => 'E'],
                        ];
                        $gradingScheme = old('grading_scheme', $seminarJenis->grading_scheme ?? $defaultScheme);
                    @endphp

                    @foreach($gradingScheme as $index => $grade)
                    <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-green-300">
                        <div class="w-16">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Grade</label>
                            <input
                                type="text"
                                name="grading_scheme[{{ $index }}][grade]"
                                value="{{ $grade['grade'] }}"
                                class="w-full px-2 py-1 text-center border border-gray-300 rounded font-bold text-lg"
                                placeholder="A"
                                required
                            >
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nilai Minimal</label>
                            <input
                                type="number"
                                name="grading_scheme[{{ $index }}][min]"
                                value="{{ $grade['min'] }}"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-1 border border-gray-300 rounded"
                                placeholder="76"
                                required
                            >
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nilai Maksimal</label>
                            <input
                                type="number"
                                name="grading_scheme[{{ $index }}][max]"
                                value="{{ $grade['max'] }}"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-1 border border-gray-300 rounded"
                                placeholder="100"
                                required
                            >
                        </div>
                        <button type="button" onclick="removeGradeRow(this)" class="text-red-600 hover:text-red-800 p-2 mt-5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>

                <button type="button" onclick="addGradeRow()" class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                    + Tambah Grade
                </button>

                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-blue-800">
                        <strong>Tips:</strong> Pastikan rentang nilai tidak tumpang tindih dan mencakup semua kemungkinan nilai (0-100).
                        Contoh standar: A (76-100), B+ (71-75.99), B (66-70.99), dst.
                    </p>
                </div>
            </div>

        <!-- Action Buttons at Bottom -->
        <div class="mt-6 flex items-center justify-between pt-6 border-t border-gray-200">
            <a href="{{ route('admin.seminarjenis.index') }}" class="btn-pill btn-pill-secondary">
                Batal
            </a>
            <button type="submit" class="btn-pill btn-pill-primary">
                Perbarui Jenis Seminar
            </button>
        </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Aspek Penilaian</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_nama_aspek" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                    <input type="text" name="nama_aspek" id="edit_nama_aspek" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div class="mb-4">
                    <label for="edit_urutan" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" id="edit_urutan" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeEditModal()" class="btn-pill btn-pill-secondary">
                        Batal
                    </button>
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function() {
    // 1. Core State & Helpers
    const STORAGE_KEY = 'seminarJenis_edit_{{ $seminarJenis->id }}';
    let gradeIndex = {{ count($gradingScheme) }};
    let berkasKeyCounter = 0;
    let saveTimeout;

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;"}[m]));
    }

    // 2. Main Logic Functions
    function syncToWeightsForm() {
        const ids = {
            'weight_nama': 'nama',
            'weight_kode': 'kode',
            'weight_keterangan': 'keterangan',
            'weight_syarat_seminar': 'syarat_seminar'
        };
        for (const [targetId, sourceId] of Object.entries(ids)) {
            const target = document.getElementById(targetId);
            const source = document.getElementById(sourceId);
            if (target && source) target.value = source.value;
        }
    }

    function saveFormData() {
        const data = {
            nama: document.getElementById('nama')?.value || '',
            kode: document.getElementById('kode')?.value || '',
            keterangan: document.getElementById('keterangan')?.value || '',
            timestamp: Date.now()
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        syncToWeightsForm();
    }

    function updateTotalWeightBottom() {
        const v1 = parseFloat(document.getElementById('p1_weight_bottom')?.value) || 0;
        const v2 = parseFloat(document.getElementById('p2_weight_bottom')?.value) || 0;
        const vm = parseFloat(document.getElementById('pembahas_weight_bottom')?.value) || 0;
        const total = v1 + v2 + vm;

        const el = document.getElementById('total-weight-bottom');
        const warn = document.getElementById('weight-warning-bottom');
        if (el) {
            el.textContent = total.toFixed(2) + '%';
            if (Math.abs(total - 100) < 0.01) {
                el.classList.replace('text-red-600', 'text-green-600');
                warn?.classList.add('hidden');
            } else {
                el.classList.replace('text-green-600', 'text-red-600');
                warn?.classList.remove('hidden');
            }
        }
    }

    function addBerkasRow(keyValue = '', labelValue = '', extValue = '', maxMbValue = '', requiredChecked = true) {
        const container = document.getElementById('berkas-items');
        if (!container) return;
        if (!keyValue) {
            berkasKeyCounter++;
            keyValue = `item_${Date.now()}_${berkasKeyCounter}`;
        }
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-12 gap-3 bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:border-blue-200 transition-all group';
        row.innerHTML = `
            <div class="md:col-span-5">
                <label class="block md:hidden text-[10px] font-bold text-gray-400 uppercase mb-1">Judul Syarat</label>
                <input type="text" name="berkas_items_label[]" value="${escapeHtml(labelValue)}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-blue-300 focus:ring-1 focus:ring-blue-100" placeholder="e.g. Scan Ijazah" required>
                <input type="hidden" name="berkas_items_key[]" value="${escapeHtml(keyValue)}">
            </div>
            <div class="md:col-span-3">
                <label class="block md:hidden text-[10px] font-bold text-gray-400 uppercase mb-1">Ekstensi (PDF, JPG, dll)</label>
                <input type="text" name="berkas_items_extensions[]" value="${escapeHtml(extValue)}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-blue-300 focus:ring-1 focus:ring-blue-100" placeholder="pdf, jpg">
            </div>
            <div class="md:col-span-2">
                <label class="block md:hidden text-[10px] font-bold text-gray-400 uppercase mb-1">Ukuran Maks (MB)</label>
                <input type="number" name="berkas_items_max_size_mb[]" value="${escapeHtml(maxMbValue)}" min="0.1" max="50" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:border-blue-300 focus:ring-1 focus:ring-blue-100" placeholder="5">
            </div>
            <div class="md:col-span-1 flex items-center justify-center">
                <label class="flex flex-col items-center">
                    <span class="md:hidden text-[10px] font-bold text-gray-400 uppercase mb-1">Wajib</span>
                    <input type="checkbox" name="berkas_items_required[]" value="1" ${requiredChecked ? 'checked' : ''} class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                </label>
            </div>
            <div class="md:col-span-1 flex items-center justify-end">
                <button type="button" class="text-gray-400 hover:text-red-500 transition-colors p-2" onclick="this.closest('.group').remove()" title="Hapus">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
    }

    // 3. Initialization Function
    function initSeminarJenisEdit() {
        const root = document.getElementById('grading-scheme-container');
        if (!root || root.dataset.initialized === 'true') return;
        
        // Weights & Auto-save
        @if(!$errors->any())
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    if ((Date.now() - data.timestamp) / (3600000) < 24) {
                        if (document.getElementById('nama')) document.getElementById('nama').value = data.nama || '';
                        if (document.getElementById('kode')) document.getElementById('kode').value = data.kode || '';
                        if (document.getElementById('keterangan')) document.getElementById('keterangan').value = data.keterangan || '';
                    }
                } catch(e) {}
            }
        @endif
        
        syncToWeightsForm();
        ['nama', 'kode', 'keterangan', 'syarat_seminar'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', () => {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(saveFormData, 1000);
            });
        });

        const p1B = document.getElementById('p1_weight_bottom');
        if (p1B) {
            p1B.addEventListener('input', updateTotalWeightBottom);
            document.getElementById('p2_weight_bottom')?.addEventListener('input', updateTotalWeightBottom);
            document.getElementById('pembahas_weight_bottom')?.addEventListener('input', updateTotalWeightBottom);
            updateTotalWeightBottom();
        }

        // Berkas Items
        const addB = document.getElementById('add-berkas-item');
        const berkasContainer = document.getElementById('berkas-items');
        if (addB && berkasContainer) {
            addB.addEventListener('click', () => addBerkasRow('', '', '', '', true));
            if (berkasContainer.children.length === 0) {
                const exEl = document.getElementById('existing-berkas-items');
                if (exEl) {
                    try {
                        let ex = JSON.parse(exEl.textContent || '[]');
                        if (typeof ex === 'object' && !Array.isArray(ex)) ex = Object.values(ex);
                        if (ex.length) {
                            ex.forEach(it => addBerkasRow(it.key, it.label, Array.isArray(it.extensions) ? it.extensions.join(', ') : '', it.max_size_kb ? Math.round(it.max_size_kb/102.4)/10 : '', it.required !== false));
                        } else { addBerkasRow(); }
                    } catch(e) { addBerkasRow(); }
                } else { addBerkasRow(); }
            }
        }

        root.dataset.initialized = 'true';
    }

    // 4. Modal & Grading Helpers (Global for onclick)
    window.editAspect = function(id, nama, urutan) {
        document.getElementById('edit_nama_aspek').value = nama;
        document.getElementById('edit_urutan').value = urutan;
        document.getElementById('editForm').action = "{{ route('admin.seminarjenis.aspects.update', [$seminarJenis, '__ID__']) }}".replace('__ID__', id);
        document.getElementById('editModal').classList.remove('hidden');
    };
    window.closeEditModal = function() { document.getElementById('editModal').classList.add('hidden'); };
    window.addGradeRow = function() {
        const c = document.getElementById('grading-scheme-container');
        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 bg-white p-3 rounded-lg border border-green-300';
        row.innerHTML = `
            <div class="w-16"><label class="block text-xs font-medium text-gray-600 mb-1">Grade</label>
                <input type="text" name="grading_scheme[${gradeIndex}][grade]" class="w-full px-2 py-1 text-center border border-gray-300 rounded font-bold text-lg" required></div>
            <div class="flex-1"><label class="block text-xs font-medium text-gray-600 mb-1">Nilai Min</label>
                <input type="number" name="grading_scheme[${gradeIndex}][min]" step="0.01" class="w-full px-3 py-1 border border-gray-300 rounded" required></div>
            <div class="flex-1"><label class="block text-xs font-medium text-gray-600 mb-1">Nilai Max</label>
                <input type="number" name="grading_scheme[${gradeIndex}][max]" step="0.01" class="w-full px-3 py-1 border border-gray-300 rounded" required></div>
            <button type="button" onclick="window.removeGradeRow(this)" class="text-red-600 p-2 mt-5">Hapus</button>
        `;
        c.appendChild(row);
        gradeIndex++;
    };
    window.removeGradeRow = function(btn) {
        if (document.querySelectorAll('#grading-scheme-container > div').length > 1) btn.closest('.flex').remove();
        else alert('Minimal 1 grade!');
    };

    // 5. Run Pattern
    if (document.readyState !== 'loading') initSeminarJenisEdit();
    else document.addEventListener('DOMContentLoaded', initSeminarJenisEdit);
    window.addEventListener('app:init', initSeminarJenisEdit);
    window.addEventListener('page-loaded', initSeminarJenisEdit);
})();
</script>
@endsection
