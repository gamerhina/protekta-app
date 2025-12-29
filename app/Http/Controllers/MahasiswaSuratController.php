<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\SuratJenis;
use App\Models\Admin;
use App\Notifications\SuratSubmittedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\SuratTemplateService;
use Illuminate\Support\Str;

class MahasiswaSuratController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $items = Surat::with(['jenis'])
            ->where(function ($q) use ($mahasiswa) {
                $q->where('pemohon_mahasiswa_id', $mahasiswa->id)
                  ->orWhere('mahasiswa_id', $mahasiswa->id);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('mahasiswa.surat.index', compact('items'));
    }

    public function create()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        
        // Get active letter types that allow mahasiswa as pemohon
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
                    return in_array('mahasiswa', (array)$sources);
                }

                return true; 
            })
            ->sortBy('nama')
            ->values();
        
        $jenisListPayload = $jenisList
            ->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama, 'form_fields' => $j->form_fields])
            ->values();

        $currentMahasiswaPayload = [
            'id' => $mahasiswa->id,
            'nama' => $mahasiswa->nama,
            'npm' => $mahasiswa->npm,
            'email' => $mahasiswa->email,
        ];

        return view('mahasiswa.surat.create', compact('jenisList', 'jenisListPayload', 'currentMahasiswaPayload'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
        ]);

        $jenis = SuratJenis::findOrFail((int) $request->input('surat_jenis_id'));
        $formFields = is_array($jenis->form_fields) ? $jenis->form_fields : [];

        $rules = [
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
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
                $options = collect($f['options'] ?? [])->map(fn($o) => (string)($o['value'] ?? ''))->toArray();
                $inRule = !empty($options) ? '|in:' . implode(',', array_map(fn($v) => str_replace(',', '\\,', $v), $options)) : '';
                $rules["form_data.$key"] = ($required ? 'required' : 'nullable') . $inRule;
            } elseif ($type === 'checkbox') {
                $options = collect($f['options'] ?? [])->map(fn($o) => (string)($o['value'] ?? ''))->toArray();
                if (!empty($options)) {
                    $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                    $rules["form_data.$key.*"] = 'in:' . implode(',', array_map(fn($v) => str_replace(',', '\\,', $v), $options));
                } else {
                    $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'boolean';
                }
            } elseif ($type === 'file') {
                $exts = array_filter(array_map('trim', (array) ($f['extensions'] ?? [])));
                $maxKb = (int) ($f['max_kb'] ?? 0);
                $rule = ($required ? 'required|' : 'nullable|') . 'file';
                if (!empty($exts)) $rule .= '|mimes:' . implode(',', $exts);
                if ($maxKb > 0) $rule .= '|max:' . $maxKb;
                $rules["form_files.$key"] = $rule;
            } else {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'string';
            }
        }

        $validated = $request->validate($rules);
        $data = $validated['form_data'] ?? [];
        $files = $validated['form_files'] ?? [];
        $mahasiswa = Auth::guard('mahasiswa')->user();

        $payload = [
            'surat_jenis_id' => $jenis->id,
            'pemohon_type' => 'mahasiswa',
            'pemohon_mahasiswa_id' => $mahasiswa->id,
            'mahasiswa_id' => $mahasiswa->id, // Often also as the main student reference
            'untuk_type' => 'umum',
            'no_surat' => null,
            'tanggal_surat' => now()->timezone('Asia/Jakarta')->toDateString(),
            'tujuan' => null,
            'perihal' => $jenis->nama,
            'isi' => null,
            'penerima_email' => null,
            'data' => [],
            'status' => 'diajukan',
        ];

        if ($dateFieldKey && !empty($data[$dateFieldKey])) {
            try { $payload['tanggal_surat'] = Carbon::parse($data[$dateFieldKey])->toDateString(); } catch (\Throwable $e) {}
        }

        foreach (['tujuan', 'perihal', 'isi', 'penerima_email', 'untuk_type'] as $k) {
            if (array_key_exists($k, $data)) {
                $payload[$k] = $data[$k];
                unset($data[$k]);
            }
        }

        $stored = [];
        foreach ($files as $k => $file) {
            if ($file) $stored[$k] = $file->store('surat-attachments', 'public');
        }

        $payload['data'] = array_merge($data, $stored);
        $surat = Surat::create($payload);

        // Notify admins
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->notify(new SuratSubmittedNotification($surat));
        }

        return redirect()->route('mahasiswa.surat.index')->with('success', 'Permohonan surat berhasil diajukan.');
    }

    private function generateDefaultNoSurat(?int $suratJenisId = null): string
    {
        $currentYear = Carbon::now()->year;
        $query = Surat::whereYear('created_at', $currentYear);
        if ($suratJenisId) $query->where('surat_jenis_id', $suratJenisId);
        $maxNumber = $query->max(DB::raw('CAST(no_surat AS UNSIGNED)'));
        $nextNumber = $maxNumber ? ((int) $maxNumber + 1) : 1;
        return str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function show(Surat $surat)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        
        $isOwner = (int) $surat->pemohon_mahasiswa_id === (int) $mahasiswa->id;
        $isTarget = (int) $surat->mahasiswa_id === (int) $mahasiswa->id;

        if (!$isOwner && !$isTarget) {
            abort(403);
        }
        
        $surat->load(['jenis']);
        return view('mahasiswa.surat.show', compact('surat'));
    }
    public function download(Surat $surat, SuratTemplateService $service)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        
        // Ownership check
        $isOwner = ($surat->pemohon_type === 'mahasiswa' && (int)$surat->pemohon_mahasiswa_id === (int)$mahasiswa->id) ||
                   ((int)$surat->mahasiswa_id === (int)$mahasiswa->id);
        
        abort_unless($isOwner, 403);
        
        $statusAllowed = in_array($surat->status, ['diproses', 'dikirim']);
        if (!$statusAllowed) {
            return redirect()->back()->with('error', 'Surat belum tersedia untuk diunduh.');
        }

        $template = $surat->jenis?->template;
        if (!$template) {
            return redirect()->back()->with('error', 'Template surat tidak ditemukan.');
        }

        $outDir = storage_path('app/public/generated-surats');
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
