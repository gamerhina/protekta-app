@extends('layouts.app')

@section('title', 'Kelola Jenis Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Kelola Jenis Surat</h1>
            <div class="flex flex-wrap gap-3 justify-center sm:justify-start">
                <a href="{{ route('admin.suratjenis.create') }}" class="btn-gradient inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Buat Jenis Surat
                </a>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Template</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $item->nama }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $item->kode }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->template_id)
                                    <span class="inline-flex text-xs px-3 py-1 font-semibold rounded-full bg-emerald-50 text-emerald-700">
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex text-xs px-3 py-1 font-semibold rounded-full bg-gray-100 text-gray-600">
                                        Belum
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <a class="hover:scale-110 transition-transform" href="{{ route('admin.suratjenis.edit', $item) }}" title="Edit" style="color: #f59e0b !important;">
                                        <i class="fas fa-edit fa-fw"></i>
                                    </a>
                                    <a class="hover:scale-110 transition-transform" href="{{ route('admin.surattemplate.index', $item) }}" title="Template" style="color: #6366f1 !important;">
                                        <i class="fas fa-file-word fa-fw"></i>
                                    </a>
                                    <form action="{{ route('admin.suratjenis.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis surat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hover:scale-110 transition-transform" title="Hapus" style="color: #f43f5e !important; border: none; background: none; padding: 0;">
                                            <i class="fas fa-trash fa-fw"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada jenis surat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
