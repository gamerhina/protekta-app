<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Surat;
use App\Models\SuratJenis;
use App\Models\Admin;
use App\Notifications\SuratSubmittedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Services\SuratTemplateService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class DosenSuratController extends Controller
{
    public function index(Request $request)
    {
        $dosen = auth('dosen')->user();
        $items = Surat::with(['jenis', 'mahasiswa'])
            ->where('pemohon_dosen_id', $dosen->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dosen.surat.index', compact('items'));
    }

    public function show(Surat $surat)
    {
        $dosen = auth('dosen')->user();

        if ($surat->pemohon_type !== 'dosen' || (int) $surat->pemohon_dosen_id !== (int) $dosen->id) {
            abort(403);
        }

        $surat->load(['jenis', 'mahasiswa']);
        $mahasiswas = Mahasiswa::orderBy('nama')->get(['id', 'nama', 'npm', 'email']);
        
        return view('dosen.surat.show', compact('surat', 'mahasiswas'));
    }

    public function create()
    {
        $dosen = auth('dosen')->user();
        
        // Get active letter types that allow dosen as pemohon
        $jenisList = SuratJenis::where('aktif', true)
            ->get()
            ->filter(function ($jenis) {
                $fields = is_array($jenis->form_fields) ? $jenis->form_fields : [];
                $pemohonField = null;
                foreach ($fields as $f) {
                    if (($f['type'] ?? '') === 'pemohon') {
                        $pemohonField = $f;
                        break;
                    }
                }

                if ($pemohonField) {
                    $sources = $pemohonField['pemohon_sources'] ?? $pemohonField['sources'] ?? ['mahasiswa', 'dosen'];
                    return in_array('dosen', (array)$sources);
                }

                return true; // Default to allow if no pemohon field defined
            })
            ->sortBy('nama')
            ->values();

        $mahasiswas = Mahasiswa::orderBy('nama')->get();

        $jenisListPayload = $jenisList
            ->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama, 'form_fields' => $j->form_fields])
            ->values();

        $mahasiswasPayload = $mahasiswas
            ->map(fn ($m) => ['id' => $m->id, 'nama' => $m->nama, 'npm' => $m->npm, 'email' => $m->email])
            ->values();

        $currentDosenPayload = [
            'id' => $dosen?->id,
            'nama' => $dosen?->nama ?? $dosen?->name ?? 'Dosen',
            'nip' => $dosen?->nip,
            'email' => $dosen?->email,
        ];

        return view('dosen.surat.create', compact('jenisList', 'mahasiswas', 'jenisListPayload', 'mahasiswasPayload', 'currentDosenPayload'));
    }

    public function store(Request $request)
    {
        $dosen = auth('dosen')->user();

        $request->validate([
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
        ]);

        $jenis = SuratJenis::findOrFail((int) $request->input('surat_jenis_id'));
        $formFields = is_array($jenis->form_fields) ? $jenis->form_fields : [];

        // Ensure pemohon field (if configured) is always set to the authenticated dosen
        $data = is_array($request->input('form_data')) ? $request->input('form_data') : [];
        foreach ($formFields as $f) {
            if (!is_array($f)) continue;
            if (($f['type'] ?? null) !== 'pemohon') continue;
            $key = trim((string) ($f['key'] ?? ''));
            if ($key === '') continue;
            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
            }
            $data[$key]['type'] = 'dosen';
            $data[$key]['id'] = (int) $dosen->id;
            break;
        }
        $request->merge(['form_data' => $data]);

        $rules = [
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
            'form_data' => 'nullable|array',
            'form_files' => 'nullable|array',
        ];

        $pemohonFieldKey = null;
        $dateFieldKey = null;

        foreach ($formFields as $f) {
            if (!is_array($f)) {
                continue;
            }

            $key = trim((string) ($f['key'] ?? ''));
            $type = trim((string) ($f['type'] ?? 'text'));
            $required = (bool) ($f['required'] ?? false);

            if ($key === '') {
                continue;
            }

            if ($type === 'pemohon') {
                $pemohonFieldKey = $pemohonFieldKey ?? $key;
                $rules["form_data.$key.type"] = ($required ? 'required|' : 'nullable|') . 'in:dosen';
                $rules["form_data.$key.id"] = ($required ? 'required|' : 'nullable|') . 'integer|min:1';
                continue;
            }

            if ($type === 'auto_no_surat') {
                continue;
            }

            if ($type === 'date') {
                $dateFieldKey = $dateFieldKey ?? $key;
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'date';
                continue;
            }

            if ($type === 'email') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'email';
                continue;
            }

            if ($type === 'number') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'numeric';
                continue;
            }

            if (in_array($type, ['select', 'radio'], true)) {
                $options = [];
                foreach ((array) ($f['options'] ?? []) as $opt) {
                    if (is_array($opt) && isset($opt['value'])) {
                        $options[] = (string) $opt['value'];
                    }
                }
                $inRule = !empty($options) ? '|in:' . implode(',', array_map(fn ($v) => str_replace(',', '\\,', $v), $options)) : '';
                $rules["form_data.$key"] = ($required ? 'required' : 'nullable') . $inRule;
                continue;
            }

            if ($type === 'checkbox') {
                $options = [];
                foreach ((array) ($f['options'] ?? []) as $opt) {
                    if (is_array($opt) && isset($opt['value'])) {
                        $options[] = (string) $opt['value'];
                    }
                }

                if (!empty($options)) {
                    $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                    $rules["form_data.$key.*"] = 'in:' . implode(',', array_map(fn ($v) => str_replace(',', '\\,', $v), $options));
                } else {
                    $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'boolean';
                }
                continue;
            }

            if ($type === 'file') {
                $exts = array_filter(array_map('trim', (array) ($f['extensions'] ?? [])));
                $maxKb = (int) ($f['max_kb'] ?? 0);

                $rule = ($required ? 'required|' : 'nullable|') . 'file';
                if (!empty($exts)) {
                    $rule .= '|mimes:' . implode(',', $exts);
                }
                if ($maxKb > 0) {
                    $rule .= '|max:' . $maxKb;
                }

                $rules["form_files.$key"] = $rule;
                continue;
            }

            $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'string';
        }

        $validated = $request->validate($rules);

        $data = is_array($validated['form_data'] ?? null) ? $validated['form_data'] : [];
        $files = is_array($validated['form_files'] ?? null) ? $validated['form_files'] : [];

        $payload = [
            'surat_jenis_id' => $jenis->id,
            'pemohon_type' => 'dosen',
            'pemohon_dosen_id' => $dosen->id,
            'pemohon_mahasiswa_id' => null,
            'mahasiswa_id' => null,
            'untuk_type' => 'umum',
            'no_surat' => null,
            'tanggal_surat' => now()->timezone('Asia/Jakarta')->toDateString(),
            'tujuan' => null,
            'perihal' => null,
            'isi' => null,
            'penerima_email' => null,
            'data' => [],
            'status' => 'diajukan',
        ];

        // tanggal surat from configured date field if present
        if ($dateFieldKey && !empty($data[$dateFieldKey])) {
            try {
                $payload['tanggal_surat'] = Carbon::parse($data[$dateFieldKey])->toDateString();
            } catch (\Throwable $e) {
                // keep default
            }
        }

        // Common mapping from well-known keys
        foreach (['tujuan', 'perihal', 'isi', 'penerima_email', 'untuk_type', 'mahasiswa_id'] as $k) {
            if (array_key_exists($k, $data)) {
                $payload[$k] = $data[$k];
                unset($data[$k]);
            }
        }

        // Dosen rules: jika untuk mahasiswa, mahasiswa_id wajib ada
        if (($payload['untuk_type'] ?? null) === 'mahasiswa' && empty($payload['mahasiswa_id'])) {
            return redirect()->back()->withInput()->withErrors(['form_data.mahasiswa_id' => 'Mahasiswa wajib dipilih untuk jenis permohonan ini.']);
        }

        if (($payload['untuk_type'] ?? null) !== 'mahasiswa') {
            $payload['mahasiswa_id'] = null;
        }

        // Handle uploads
        $stored = [];
        foreach ($files as $k => $file) {
            if (!$file) continue;
            $stored[$k] = $file->store('documents/surat/attachments', 'public');
        }

        $payload['data'] = array_merge($data, $stored);

        $surat = Surat::create($payload);

        // Send notification to all admins
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->notify(new SuratSubmittedNotification($surat));
        }

        return redirect()->route('dosen.surat.index')->with('success', 'Permohonan surat berhasil dikirim.');
    }

    public function update(Request $request, Surat $surat)
    {
        $dosen = auth('dosen')->user();

        if ($surat->pemohon_type !== 'dosen' || (int) $surat->pemohon_dosen_id !== (int) $dosen->id) {
            abort(403);
        }

        if ($surat->status !== 'diajukan') {
            return redirect()->route('dosen.surat.show', $surat)->with('error', 'Permohonan yang sudah diproses tidak dapat diubah.');
        }

        $jenis = $surat->jenis;
        $formFields = is_array($jenis->form_fields) ? $jenis->form_fields : [];

        $rules = [
            'form_data' => 'nullable|array',
            'form_files' => 'nullable|array',
        ];

        $dateFieldKey = null;
        foreach ($formFields as $f) {
            if (!is_array($f)) continue;
            $key = trim((string) ($f['key'] ?? ''));
            $type = trim((string) ($f['type'] ?? 'text'));
            $required = (bool) ($f['required'] ?? false);
            if ($key === '' || $type === 'pemohon' || $type === 'auto_no_surat') continue;

            if ($type === 'date') {
                $dateFieldKey = $dateFieldKey ?? $key;
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'date';
            } elseif ($type === 'email') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'email';
            } elseif ($type === 'number') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'numeric';
            } elseif (in_array($type, ['select', 'radio'], true)) {
                $options = [];
                foreach ((array) ($f['options'] ?? []) as $opt) {
                    if (is_array($opt) && isset($opt['value'])) $options[] = (string) $opt['value'];
                }
                $inRule = !empty($options) ? '|in:' . implode(',', array_map(fn ($v) => str_replace(',', '\\,', $v), $options)) : '';
                $rules["form_data.$key"] = ($required ? 'required' : 'nullable') . $inRule;
            } elseif ($type === 'checkbox') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . (isset($f['options']) && !empty($f['options']) ? 'array' : 'boolean');
            } elseif ($type === 'file') {
                $exts = array_filter(array_map('trim', (array) ($f['extensions'] ?? [])));
                $maxKb = (int) ($f['max_kb'] ?? 0);
                $rule = 'nullable|file'; // Files are always optional on update
                if (!empty($exts)) $rule .= '|mimes:' . implode(',', $exts);
                if ($maxKb > 0) $rule .= '|max:' . $maxKb;
                $rules["form_files.$key"] = $rule;
            } else {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'string';
            }
        }

        $validated = $request->validate($rules);
        $data = is_array($validated['form_data'] ?? null) ? $validated['form_data'] : [];
        $files = is_array($validated['form_files'] ?? null) ? $validated['form_files'] : [];

        $payload = [
            'tanggal_surat' => $surat->tanggal_surat,
            'tujuan' => $surat->tujuan,
            'perihal' => $surat->perihal,
            'isi' => $surat->isi,
            'penerima_email' => $surat->penerima_email,
            'untuk_type' => $surat->untuk_type,
            'mahasiswa_id' => $surat->mahasiswa_id,
        ];

        if ($dateFieldKey && !empty($data[$dateFieldKey])) {
            try { $payload['tanggal_surat'] = Carbon::parse($data[$dateFieldKey])->toDateString(); } catch (\Throwable $e) {}
        }

        foreach (['tujuan', 'perihal', 'isi', 'penerima_email', 'untuk_type', 'mahasiswa_id'] as $k) {
            if (array_key_exists($k, $data)) {
                $payload[$k] = $data[$k];
                unset($data[$k]);
            }
        }

        if (($payload['untuk_type'] ?? null) === 'mahasiswa' && empty($payload['mahasiswa_id'])) {
            return redirect()->back()->withInput()->withErrors(['form_data.mahasiswa_id' => 'Mahasiswa wajib dipilih untuk jenis permohonan ini.']);
        }

        if (($payload['untuk_type'] ?? null) !== 'mahasiswa') $payload['mahasiswa_id'] = null;

        $stored = [];
        foreach ($files as $k => $file) {
            if ($file) $stored[$k] = $file->store('documents/surat/attachments', 'public');
        }

        $payload['data'] = array_merge($surat->data ?? [], $data, $stored);
        $surat->update($payload);

        return redirect()->route('dosen.surat.index')->with('success', 'Permohonan surat berhasil diperbarui.');
    }

    public function destroy(Surat $surat)
    {
        $dosen = auth('dosen')->user();

        if ($surat->pemohon_type !== 'dosen' || (int) $surat->pemohon_dosen_id !== (int) $dosen->id) {
            abort(403);
        }

        if ($surat->status !== 'diajukan') {
            return redirect()->route('dosen.surat.show', $surat)->with('error', 'Permohonan yang sudah diproses tidak dapat dibatalkan.');
        }

        $surat->delete();

        return redirect()->route('dosen.surat.index')->with('success', 'Permohonan surat berhasil dibatalkan.');
    }
    public function download(Surat $surat, SuratTemplateService $service)
    {
        $dosen = auth('dosen')->user();
        abort_unless($surat->pemohon_type === 'dosen' && (int)$surat->pemohon_dosen_id === (int)$dosen->id, 403);
        
        $statusAllowed = in_array($surat->status, ['diproses', 'dikirim']);
        if (!$statusAllowed) {
            return redirect()->back()->with('error', 'Surat belum tersedia untuk diunduh.');
        }

        $template = $surat->jenis?->template;
        if (!$template) {
            return redirect()->back()->with('error', 'Template surat tidak ditemukan.');
        }

        $outDir = public_path('uploads/documents/surat/generated');
        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }
        
        $safeName = Str::slug($surat->jenis->nama . '_' . ($surat->no_surat ?? $surat->id)) ?: 'surat';
        $filename = $safeName . '_' . time() . '.docx';
        $outPath = $outDir . '/' . $filename;

        try {
            $service->generateDocx($template, $surat, $outPath);
            if (!file_exists($outPath)) {
                throw new \Exception("File failed to generate.");
            }
            return response()->download($outPath, $filename);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mengunduh surat: ' . $e->getMessage());
        }
    }
}
