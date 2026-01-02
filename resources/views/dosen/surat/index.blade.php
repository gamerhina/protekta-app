@extends('layouts.app')

@section('title', 'Permohonan Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Permohonan Surat</h1>
            <a href="{{ route('dosen.surat.create') }}" class="btn-gradient inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> Buat Permohonan
            </a>
        </div>

        <form method="GET" class="mb-6">
            <div class="bg-white/70 backdrop-blur border border-gray-100 rounded-2xl shadow-inner p-4 md:p-5">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                    </span>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari permohonan surat (No. Surat, Jenis, Perihal, atau Tujuan)..."
                           class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
            </div>
        </form>

        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">No. Surat</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($items as $s)
                        <tr>
                            <td class="px-6 py-4 text-sm font-mono text-gray-700">{{ $s->no_surat ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ $s->jenis->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $s->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($s->status == 'diajukan') bg-yellow-100 text-yellow-800
                                    @elseif($s->status == 'diproses') bg-blue-100 text-blue-800
                                    @elseif($s->status == 'dikirim') bg-green-100 text-green-800
                                    @elseif($s->status == 'ditolak') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('dosen.surat.show', $s) }}" class="text-blue-500 hover:text-blue-700 transition-colors" title="Lihat/Edit">
                                        <i class="fas fa-eye text-lg"></i>
                                    </a>
                                    @if(in_array($s->status, ['diproses', 'dikirim']) && ($s->jenis->allow_download ?? true))
                                        <a href="{{ route('dosen.surat.download', $s) }}" class="text-green-600 hover:text-green-800 transition-colors" title="Unduh Surat" download data-no-ajax>
                                            <i class="fas fa-file-download text-lg"></i>
                                        </a>
                                    @endif
                                    @if($s->status === 'diajukan')
                                        <form action="{{ route('dosen.surat.destroy', $s) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Batalkan Permohonan">
                                                <i class="fas fa-trash text-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada permohonan surat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="mt-6">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
