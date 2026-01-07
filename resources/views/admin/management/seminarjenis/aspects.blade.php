@extends('layouts.app')

@section('title', 'Kelola Aspek Penilaian')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Aspek Penilaian</h1>
                    <p class="text-gray-600 mt-1">{{ $seminarJenis->nama }} ({{ $seminarJenis->kode }})</p>
                </div>
                <a href="{{ route('admin.seminarjenis.index') }}" class="btn-pill btn-pill-secondary justify-center sm:justify-start">
                    Kembali
                </a>
            </div>
        </div>

        <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-medium text-blue-800 mb-2">Tambah Aspek Penilaian Baru</h3>
            <form action="{{ route('admin.seminarjenis.aspects.store', $seminarJenis) }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @csrf
                <div>
                    <label for="evaluator_type" class="block text-sm font-medium text-gray-700 mb-1">Penilai</label>
                    <select name="evaluator_type" id="evaluator_type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="">Pilih Penilai</option>
                        <option value="p1">Pembimbing 1 (P1)</option>
                        <option value="p2">Pembimbing 2 (P2)</option>
                        <option value="pembahas">Pembahas (PMB)</option>
                    </select>
                </div>
                <div>
                    <label for="nama_aspek" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                    <input type="text" name="nama_aspek" id="nama_aspek" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Nilai Penguasaan Materi" required>
                </div>
                <div>
                    <label for="persentase" class="block text-sm font-medium text-gray-700 mb-1">Persentase (%)</label>
                    <input type="number" name="persentase" id="persentase" min="0" max="100" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="20" required>
                </div>
                <div>
                    <label for="urutan" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" id="urutan" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="1" required>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full btn-pill btn-pill-primary">
                        Tambah
                    </button>
                </div>
            </form>
        </div>

        @foreach(['p1' => 'Pembimbing 1 (P1)', 'p2' => 'Pembimbing 2 (P2)', 'pembahas' => 'Pembahas (PMB)'] as $type => $label)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ $label }}</h2>
                
                @if(isset($aspects[$type]) && $aspects[$type]->count() > 0)
                    @php
                        $totalPercentage = $aspects[$type]->sum('persentase');
                    @endphp
                    
                    <div class="mb-4 p-3 rounded-md {{ $totalPercentage == 100 ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' }}">
                        <p class="text-sm font-medium {{ $totalPercentage == 100 ? 'text-green-800' : 'text-yellow-800' }}">
                            Total Persentase: {{ $totalPercentage }}%
                            @if($totalPercentage != 100)
                                <span class="ml-2">(Harus 100%)</span>
                            @endif
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Urutan</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Aspek</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Persentase</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($aspects[$type] as $aspect)
                                <tr>
                                    <td class="px-4 py-3">{{ $aspect->urutan }}</td>
                                    <td class="px-4 py-3">{{ $aspect->nama_aspek }}</td>
                                    <td class="px-4 py-3">{{ $aspect->persentase }}%</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex space-x-2">
                                            <button onclick="editAspect({{ $aspect->id }}, '{{ $aspect->nama_aspek }}', {{ $aspect->persentase }}, {{ $aspect->urutan }})" class="text-blue-600 hover:text-blue-900 font-semibold" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.seminarjenis.aspects.destroy', [$seminarJenis, $aspect]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aspek ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-6 text-center">
                        <p class="text-gray-500">Belum ada aspek penilaian untuk {{ $label }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ubah Aspek Penilaian</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_nama_aspek" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                    <input type="text" name="nama_aspek" id="edit_nama_aspek" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div class="mb-4">
                    <label for="edit_persentase" class="block text-sm font-medium text-gray-700 mb-1">Persentase (%)</label>
                    <input type="number" name="persentase" id="edit_persentase" min="0" max="100" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
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

@section('scripts')
<script>
(function() {
    window.editAspect = function(id, nama, persentase, urutan) {
        document.getElementById('edit_nama_aspek').value = nama;
        document.getElementById('edit_persentase').value = persentase;
        document.getElementById('edit_urutan').value = urutan;
        document.getElementById('editForm').action = '{{ route('admin.seminarjenis.aspects.update', [$seminarJenis, '__ID__']) }}'.replace('__ID__', id);
        document.getElementById('editModal').classList.remove('hidden');
    }

    window.closeEditModal = function() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function initAspectsPage() {
        const modal = document.getElementById('editModal');
        if (!modal || modal.dataset.initialized === 'true') return;

        modal.addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        modal.dataset.initialized = 'true';
    }

    if (document.readyState !== 'loading') initAspectsPage();
    else document.addEventListener('DOMContentLoaded', initAspectsPage);
    window.addEventListener('page-loaded', initAspectsPage);
})();
</script>
@endsection
