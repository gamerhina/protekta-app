@extends('layouts.app')

@section('title', 'Manage Home')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Manage Home Page</h1>
            <p class="text-sm text-slate-500">Atur konten, tampilan visual, dan warna landing page aplikasi.</p>
        </div>
        <div class="flex gap-2 flex-wrap sm:flex-nowrap">
            <a href="{{ route('admin.dashboard') }}" class="btn-pill btn-pill-secondary !no-underline">
                Batal
            </a>
            <button type="submit" form="settingsForm" class="btn-pill btn-pill-primary inline-flex items-center gap-2">
                Simpan Perubahan
            </button>
        </div>
    </div>

    <div class="mt-6">
        <form id="settingsForm" method="POST" action="{{ route('admin.settings.landing.update') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @method('PUT')
        
        <!-- Left Column: Content & Assets (Span 8) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- General Content Section -->
            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Konten Utama
                </h2>
                <div class="grid gap-5">
                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Aplikasi</label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings->app_name) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors placeholder-slate-400" placeholder="Protekta Apps">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Header</label>
                            <input type="text" name="hero_title" value="{{ old('hero_title', $settings->hero_title) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors placeholder-slate-400" placeholder="Judul utama">
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Subjudul Singkat</label>
                        <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $settings->hero_subtitle) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors placeholder-slate-400">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Deskripsi Aplikasi</label>
                        <textarea name="app_description" rows="2" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors placeholder-slate-400 resize-none">{{ old('app_description', $settings->app_description) }}</textarea>
                    </div>

                    <div class="grid md:grid-cols-3 gap-5 pt-2 border-t border-slate-100">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Label Tombol CTA</label>
                            <input type="text" name="cta_label" value="{{ old('cta_label', $settings->cta_label) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Link CTA</label>
                            <input type="text" name="cta_link" value="{{ old('cta_link', $settings->cta_link) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors" placeholder="/login">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Jadwal</label>
                            <input type="text" name="schedule_heading" value="{{ old('schedule_heading', $settings->schedule_heading) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Assets Section -->
            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Aset Visual & Background
                </h2>
                
                <!-- Logos Row -->
                <div class="grid md:grid-cols-3 gap-6 mb-8">
                    @foreach(['app_icon' => 'Icon Dashboard', 'logo' => 'Logo Utama', 'favicon' => 'Favicon'] as $field => $label)
                        <div class="relative group">
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $label }}</label>
                                @if($settings->{$field . '_url'})
                                    <button type="button" data-remove-asset="remove_{{ $field }}" class="text-[10px] font-bold text-red-500 hover:text-red-600 bg-red-50 px-2 py-0.5 rounded-md transition-colors">HAPUS</button>
                                @endif
                            </div>
                            <input type="hidden" name="remove_{{ $field }}" value="0">
                            <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors p-4 text-center group-hover:border-blue-400">
                                <input type="file" name="{{ $field }}" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                @if($settings->{$field . '_url'})
                                    <img src="{{ $settings->{$field . '_url'} }}" class="h-12 w-auto mx-auto object-contain mb-2" alt="{{ $label }}">
                                    <p class="text-[10px] text-slate-400 truncate">Klik untuk ganti</p>
                                @else
                                    <div class="h-12 flex items-center justify-center text-slate-300 mb-2">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium">Upload {{ $label }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Backgrounds Grid -->
                <div class="grid md:grid-cols-2 gap-6 pt-6 border-t border-slate-100">
                    <!-- Header Slider (Full Width) -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                                Slider Header
                                @if(!empty($settings->landing_background_slides) || $settings->landing_background_url)
                                    <span class="inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                                @endif
                            </label>
                        </div>
                        <input type="hidden" name="remove_landing_background" value="0">

                        @if(isset($sliderReady) && !$sliderReady)
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
                                Slider belum aktif karena kolom database belum ada. Jalankan: <span class="font-mono">php artisan migrate</span>
                            </div>
                        @endif

                        <div class="bg-slate-50 rounded-xl p-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-semibold text-slate-700">Aktifkan Slider</label>
                                <div>
                                    <input type="hidden" name="landing_slider_enabled" value="0">
                                    <input type="checkbox" name="landing_slider_enabled" value="1" {{ old('landing_slider_enabled', $settings->landing_slider_enabled ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase">Interval (ms)</label>
                                    <input type="number" name="landing_slider_interval_ms" min="2000" max="20000" step="500" value="{{ old('landing_slider_interval_ms', $settings->landing_slider_interval_ms ?? 6000) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] font-bold text-slate-500 uppercase">Upload Slide</label>
                                        <button type="button" id="add_slide_upload" class="text-[10px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-2 py-1 rounded-md transition-colors">+ Tambah Gambar</button>
                                    </div>
                                    <div id="slide_upload_list" class="space-y-2">
                                        <input type="file" name="landing_slides[]" accept="image/*" class="w-full rounded-xl border-slate-200 bg-white px-3 py-2 text-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-4">
                                <p class="text-xs font-semibold text-slate-800">Tambah Upload Gambar Slide</p>
                                <p class="mt-1 text-[11px] text-slate-500">Pilih 1+ gambar sekaligus (JPG/PNG). Setelah upload, klik <b>Simpan Perubahan</b>.</p>
                            </div>
                            <p class="text-[11px] text-slate-500">Tips: urutan slide mengikuti nilai Order (kecil → besar).</p>
                        </div>

                        @php
                            $slidePaths = old('slides_existing') ? collect(old('slides_existing'))->pluck('path')->filter()->values()->all() : ($settings->landing_background_slides ?? []);
                        @endphp

                        @if(!empty($slidePaths))
                            <div class="space-y-3">
                                @foreach($slidePaths as $i => $path)
                                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                        <div class="h-14 w-24 overflow-hidden rounded-lg bg-slate-200 flex-shrink-0">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('uploads')->url($path) }}" class="w-full h-full object-cover" alt="Slide {{ $i + 1 }}">
                                        </div>
                                        <div class="flex-1 grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 uppercase">Order</label>
                                                <input type="number" name="slides_existing[{{ $i }}][order]" min="1" max="999" value="{{ old("slides_existing.$i.order", $i + 1) }}" class="mt-1 w-full rounded-lg border-slate-200 px-2 py-1.5 text-xs">
                                            </div>
                                            <div class="flex items-end">
                                                <label class="flex items-center gap-2 text-xs text-slate-600">
                                                    <input type="checkbox" name="slides_existing[{{ $i }}][remove]" value="1">
                                                    Hapus
                                                </label>
                                            </div>
                                        </div>
                                        <input type="hidden" name="slides_existing[{{ $i }}][path]" value="{{ $path }}">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center text-xs text-slate-500">
                                Belum ada slide header.
                            </div>
                        @endif

                        <details class="rounded-xl border border-slate-200 bg-white">
                            <summary class="cursor-pointer px-4 py-3 text-xs font-semibold text-slate-700">Fallback: Background Header (Single)</summary>
                            <div class="px-4 pb-4 space-y-3">
                                <div class="relative rounded-xl border border-slate-200 bg-slate-50 p-1 group">
                                    <div class="h-24 w-full rounded-lg bg-slate-200 overflow-hidden relative">
                                        @if($settings->landing_background_url)
                                            <img src="{{ $settings->landing_background_url }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="flex items-center justify-center h-full text-slate-400">
                                                <span class="text-xs">No image</span>
                                            </div>
                                        @endif
                                        <input type="file" name="landing_background" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                            <span class="text-white text-xs font-semibold">Ganti Gambar</span>
                                        </div>
                                    </div>
                                </div>
                                @if($settings->landing_background_url)
                                    <button type="button" data-remove-asset="remove_landing_background" class="text-[10px] font-bold text-red-500 hover:text-red-600 bg-red-50 px-2 py-1 rounded-md transition-colors">HAPUS Background Single</button>
                                @endif
                            </div>
                        </details>
                        
                        <div class="bg-slate-50 rounded-xl p-3 space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Tinggi (px)</label>
                                <span class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ old('header_height', $settings->header_height ?? 500) }}px</span>
                            </div>
                            <input type="range" name="header_height" min="300" max="800" value="{{ old('header_height', $settings->header_height ?? 500) }}" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="this.previousElementSibling.lastElementChild.textContent = this.value + 'px'">
                        </div>
                    </div>

                    <!-- Content Background -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                                Background Konten
                                @if($settings->content_background_url)
                                    <span class="inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                                @endif
                            </label>
                            @if($settings->content_background_url)
                                <button type="button" data-remove-asset="remove_content_background" class="text-[10px] font-bold text-red-500 hover:text-red-600 bg-red-50 px-2 py-0.5 rounded-md transition-colors">HAPUS</button>
                            @endif
                        </div>
                        <input type="hidden" name="remove_content_background" value="0">

                        <div class="relative rounded-xl border border-slate-200 bg-slate-50 p-1 group">
                            <div class="h-24 w-full rounded-lg bg-slate-200 overflow-hidden relative">
                                @if($settings->content_background_url)
                                    <img src="{{ $settings->content_background_url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="flex items-center justify-center h-full text-slate-400">
                                        <span class="text-xs">No image</span>
                                    </div>
                                @endif
                                <input type="file" name="content_background" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    <span class="text-white text-xs font-semibold">Ganti Gambar</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-3 space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Opasitas</label>
                                <span class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ old('content_background_opacity', $settings->content_background_opacity ?? 0.92) }}</span>
                            </div>
                            <input type="range" name="content_background_opacity" min="0" max="1" step="0.05" value="{{ old('content_background_opacity', $settings->content_background_opacity ?? 0.92) }}" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="this.previousElementSibling.lastElementChild.textContent = this.value">
                        </div>
                    </div>

                    <!-- Login Background (Full Width) -->
                    <div class="md:col-span-2 space-y-4 pt-4 border-t border-slate-100">
                         <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                                Background Login
                                @if($settings->login_background_url)
                                    <span class="inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                                @endif
                            </label>
                            @if($settings->login_background_url)
                                <button type="button" data-remove-asset="remove_login_background" class="text-[10px] font-bold text-red-500 hover:text-red-600 bg-red-50 px-2 py-0.5 rounded-md transition-colors">HAPUS</button>
                            @endif
                        </div>
                        <input type="hidden" name="remove_login_background" value="0">
                        
                        <div class="relative group rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors">
                            <input type="file" name="login_background" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="p-4 flex items-center gap-4">
                                <div class="h-16 w-24 rounded-lg bg-slate-200 overflow-hidden flex-shrink-0">
                                    @if($settings->login_background_url)
                                        <img src="{{ $settings->login_background_url }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="flex items-center justify-center h-full text-slate-400 text-xs">No img</div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700 group-hover:text-blue-600 transition-colors">Upload Background Login</p>
                                    <p class="text-xs text-slate-400">Format JPG/PNG, Max 4MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Styling (Span 4) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Theme Colors -->
            <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                    Warna Tema
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        'primary_color' => 'Primary',
                        'secondary_color' => 'Secondary',
                        'accent_color' => 'Accent',
                        'button_color' => 'CTA Button'
                    ] as $key => $label)
                    <div class="p-3 rounded-2xl border border-slate-100 bg-slate-50">
                        <label class="text-[10px] font-bold text-slate-500 uppercase block mb-2">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="{{ $key }}" value="{{ old($key, $settings->$key ?? '#000000') }}" class="h-8 w-8 rounded-lg border border-slate-200 cursor-pointer p-0.5 bg-white">
                            <input type="text" name="{{ $key }}_text" value="{{ old($key, $settings->$key ?? '#000000') }}" class="flex-1 w-full min-w-0 rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-mono text-center uppercase" maxlength="7">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Overlay Settings -->
            <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Hero Overlay
                </h2>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                         <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Gradient Dari</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="header_overlay_from" value="{{ old('header_overlay_from', $settings->header_overlay_from ?? '#0f172a') }}" class="h-8 w-full rounded-lg border border-slate-200 cursor-pointer p-0.5 bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Gradient Ke</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="header_overlay_to" value="{{ old('header_overlay_to', $settings->header_overlay_to ?? '#172554') }}" class="h-8 w-full rounded-lg border border-slate-200 cursor-pointer p-0.5 bg-white">
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-50">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Opasitas Overlay</label>
                            <span class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ old('hero_overlay_opacity', $settings->hero_overlay_opacity ?? 0.9) }}</span>
                        </div>
                        <input type="range" name="hero_overlay_opacity" step="0.05" min="0" max="1" value="{{ old('hero_overlay_opacity', $settings->hero_overlay_opacity ?? 0.9) }}" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="this.previousElementSibling.lastElementChild.textContent = this.value">
                    </div>
                     <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Opasitas Background</label>
                            <span class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ old('landing_background_opacity', $settings->landing_background_opacity ?? 0.95) }}</span>
                        </div>
                        <input type="range" name="landing_background_opacity" step="0.05" min="0" max="1" value="{{ old('landing_background_opacity', $settings->landing_background_opacity ?? 0.95) }}" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="this.previousElementSibling.lastElementChild.textContent = this.value">
                    </div>
                </div>
            </div>

            <!-- Table Style -->
            <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    Tampilan Tabel
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        'table_header_from' => 'Header From',
                        'table_header_to' => 'Header To',
                        'table_header_text_color' => 'Header Text',
                        'table_row_odd_color' => 'Baris Ganjil',
                        'table_row_even_color' => 'Baris Genap',
                        'table_row_text_color' => 'Teks Baris',
                        'table_border_color' => 'Border'
                    ] as $key => $label)
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1 truncate" title="{{ $label }}">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="{{ $key }}" value="{{ old($key, $settings->$key ?? '#000000') }}" class="h-8 w-full rounded-lg border border-slate-200 cursor-pointer p-0.5 bg-white">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function() {
    function initLandingSettings() {
        const addBtn = document.getElementById('add_slide_upload');
        const uploadList = document.getElementById('slide_upload_list');
        
        if (!addBtn || addBtn.dataset.initialized === 'true') return;

        addBtn.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2';
            row.innerHTML = `
                <input type="file" name="landing_slides[]" accept="image/*" class="flex-1 w-full rounded-xl border-slate-200 bg-white px-3 py-2 text-sm" />
                <button type="button" class="remove-slide-upload text-[10px] font-bold text-red-600 hover:text-red-700 bg-red-50 px-2 py-1 rounded-md">Hapus</button>
            `;
            uploadList.appendChild(row);
            row.querySelector('.remove-slide-upload')?.addEventListener('click', () => row.remove());
        });

        document.querySelectorAll('[data-remove-asset]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.dataset.removeAsset;
                const hidden = document.querySelector(`input[name="${target}"]`);
                if (!hidden) return;
                if (confirm('Hapus file ini?')) {
                    hidden.value = '1';
                    button.closest('form').submit();
                }
            });
        });
        
        document.querySelectorAll('input[type="color"]').forEach(input => {
            const textInput = document.querySelector(`input[name="${input.name}_text"]`);
            if (textInput) {
                input.addEventListener('input', (e) => textInput.value = e.target.value);
                textInput.addEventListener('input', (e) => input.value = e.target.value);
            }
        });

        addBtn.dataset.initialized = 'true';
    }

    if (document.readyState !== 'loading') initLandingSettings();
    else document.addEventListener('DOMContentLoaded', initLandingSettings);
    window.addEventListener('page-loaded', initLandingSettings);
})();
</script>
@endsection
