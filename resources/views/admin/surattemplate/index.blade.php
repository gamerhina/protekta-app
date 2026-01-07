@extends('layouts.app')

@section('title', 'Template Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Template Surat</h1>
                <p class="text-sm text-gray-500">Jenis surat: <strong>{{ $suratJenis->nama }}</strong></p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.suratjenis.edit', $suratJenis) }}" class="btn-pill btn-pill-secondary">Kembali</a>
                <a href="{{ route('admin.surattemplate.create', $suratJenis) }}" class="btn-pill btn-pill-primary">Upload Template</a>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Tag</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aktif</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($templates as $t)
                        @php
                            $tags = is_array($t->available_tags) ? $t->available_tags : [];
                        @endphp
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="font-medium">{{ $t->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $t->file_path }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ count($tags) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($suratJenis->template_id === $t->id)
                                    <span class="text-green-700">Default</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a class="text-blue-600 hover:text-blue-800 transition-colors" href="{{ route('admin.surattemplate.edit', [$suratJenis, $t]) }}" title="Kelola Template">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada template.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
