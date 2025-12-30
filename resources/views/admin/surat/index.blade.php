@extends('layouts.app')

@section('title', 'Manage Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Manage Surat</h1>
            <div class="flex flex-wrap gap-3 justify-center sm:justify-start">
                <a href="{{ route('admin.surat.export') }}" download data-no-ajax class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-green-600 to-green-700 px-7 py-2.5 text-sm font-semibold text-white shadow-lg shadow-green-500/30 transition-all hover:-translate-y-0.5 hover:shadow-green-600/50">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('admin.surat.create') }}" class="btn-gradient inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Buat Surat
                </a>
            </div>
        </div>

        <form method="GET" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <input name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Cari nomor/tujuan/perihal" onchange="this.form.submit()">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md" onchange="this.form.submit()">
                    <option value="">(Semua status)</option>
                    @foreach(['diajukan','diproses','dikirim','ditolak'] as $st)
                        <option value="{{ $st }}" {{ request('status')===$st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="sort" value="{{ request('sort', $sort ?? 'created_at') }}">
                <input type="hidden" name="direction" value="{{ request('direction', $direction ?? 'desc') }}">
            </div>
        </form>

        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'no_surat', 'direction' => (request('sort') == 'no_surat' && request('direction') == 'asc') ? 'desc' : 'asc']) }}">
                                Nomor Surat @if(request('sort') == 'no_surat') <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i> @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'pemohon', 'direction' => (request('sort') == 'pemohon' && request('direction') == 'asc') ? 'desc' : 'asc']) }}">
                                Pemohon @if(request('sort') == 'pemohon') <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i> @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'surat_jenis_id', 'direction' => (request('sort') == 'surat_jenis_id' && request('direction') == 'asc') ? 'desc' : 'asc']) }}">
                                Jenis @if(request('sort') == 'surat_jenis_id') <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i> @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'tanggal_surat', 'direction' => (request('sort') == 'tanggal_surat' && request('direction') == 'asc') ? 'desc' : 'asc']) }}">
                                Tanggal @if(request('sort') == 'tanggal_surat') <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i> @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => (request('sort') == 'status' && request('direction') == 'asc') ? 'desc' : 'asc']) }}">
                                Status @if(request('sort') == 'status') <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i> @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($items as $s)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $s->no_surat ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $s->pemohon_type === 'mahasiswa' ? ($s->pemohonMahasiswa->nama ?? '-') : ($s->pemohonDosen->nama ?? '-') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $s->jenis->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $s->tanggal_surat?->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($s->status == 'diajukan') bg-yellow-100 text-yellow-800
                                    @elseif($s->status == 'diproses') bg-blue-100 text-blue-800
                                    @elseif($s->status == 'dikirim') bg-green-100 text-green-800
                                    @elseif($s->status == 'ditolak') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.surat.show', $s) }}" class="text-blue-600 hover:text-blue-800 font-semibold" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.surat.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Hapus permohonan surat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada surat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $items->links() }}</div>
    </div>
</div>
@endsection
