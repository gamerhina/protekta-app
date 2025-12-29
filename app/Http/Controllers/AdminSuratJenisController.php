<?php

namespace App\Http\Controllers;

use App\Models\SuratJenis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminSuratJenisController extends Controller
{
    public function index()
    {
        $items = SuratJenis::orderBy('nama')->get();
        return view('admin.suratjenis.index', compact('items'));
    }

    public function create()
    {
        return view('admin.suratjenis.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|alpha_dash|unique:surat_jenis,kode',
            'keterangan' => 'nullable|string',
            'form_fields' => 'nullable|array',
            'aktif' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
        ]);

        $validated['aktif'] = $request->boolean('aktif');
        $validated['allow_download'] = $request->boolean('allow_download');

        if (Schema::hasColumn('surat_jenis', 'form_fields')) {
            $validated['form_fields'] = $this->normalizeFormFields($request->input('form_fields', []));
        } else {
            unset($validated['form_fields']);
        }

        SuratJenis::create($validated);

        return redirect()->route('admin.suratjenis.index')->with('success', 'Jenis surat berhasil dibuat.');
    }

    public function edit(SuratJenis $suratJenis)
    {
        $suratJenis->load('template');
        return view('admin.suratjenis.edit', compact('suratJenis'));
    }

    public function update(Request $request, SuratJenis $suratJenis)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|alpha_dash|unique:surat_jenis,kode,' . $suratJenis->id,
            'keterangan' => 'nullable|string',
            'form_fields' => 'nullable|array',
            'aktif' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
        ]);

        $validated['aktif'] = $request->boolean('aktif');
        $validated['allow_download'] = $request->boolean('allow_download');

        if (Schema::hasColumn('surat_jenis', 'form_fields')) {
            $validated['form_fields'] = $this->normalizeFormFields($request->input('form_fields', []));
        } else {
            unset($validated['form_fields']);
        }

        $suratJenis->update($validated);

        return redirect()->route('admin.suratjenis.edit', $suratJenis)->with('success', 'Jenis surat berhasil diperbarui.');
    }

    public function destroy(SuratJenis $suratJenis)
    {
        $suratJenis->delete();
        return redirect()->route('admin.suratjenis.index')->with('success', 'Jenis surat berhasil dihapus.');
    }

    private function normalizeFormFields($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            $key = trim((string) ($item['key'] ?? ''));
            $type = trim((string) ($item['type'] ?? 'text'));

            if ($label === '' || $key === '') {
                continue;
            }

            $field = [
                'label' => $label,
                'key' => $key,
                'type' => $type,
                'placeholder' => (string) ($item['placeholder'] ?? ''),
                'required' => (bool) ($item['required'] ?? false),
            ];

            if ($type === 'pemohon') {
                $sources = $item['pemohon_sources'] ?? $item['sources'] ?? [];
                if (is_string($sources)) {
                    $sources = preg_split('/\s*,\s*/', trim($sources)) ?: [];
                }
                $sources = array_values(array_unique(array_filter(array_map('trim', (array) $sources))));
                $sources = array_values(array_intersect($sources, ['mahasiswa', 'dosen']));
                if (empty($sources)) {
                    $sources = ['mahasiswa', 'dosen'];
                }
                $field['pemohon_sources'] = $sources;
            }

            if (in_array($type, ['select', 'radio', 'checkbox'], true)) {
                $optionsRaw = $item['options'] ?? [];
                if (is_string($optionsRaw)) {
                    $optionsRaw = preg_split('/\r\n|\r|\n/', $optionsRaw) ?: [];
                }
                $options = [];
                foreach ((array) $optionsRaw as $line) {
                    $line = trim((string) $line);
                    if ($line === '') {
                        continue;
                    }
                    if (str_contains($line, '|')) {
                        [$val, $lbl] = array_map('trim', explode('|', $line, 2));
                        $options[] = ['value' => $val, 'label' => $lbl !== '' ? $lbl : $val];
                    } else {
                        $options[] = ['value' => $line, 'label' => $line];
                    }
                }
                $field['options'] = $options;
            }

            if ($type === 'file') {
                $extRaw = $item['extensions'] ?? '';
                $extensions = [];
                if (is_string($extRaw)) {
                    $extensions = array_filter(array_map('trim', explode(',', $extRaw)));
                } elseif (is_array($extRaw)) {
                    $extensions = array_filter(array_map('trim', $extRaw));
                }
                $field['extensions'] = array_values($extensions);
                $field['max_kb'] = (int) ($item['max_kb'] ?? 0);
            }

            $out[] = $field;
        }

        return $out;
    }
}
