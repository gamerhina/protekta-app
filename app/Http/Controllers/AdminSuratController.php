<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\SuratJenis;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Admin;
use App\Notifications\SuratSubmittedNotification;
use App\Notifications\SuratStatusUpdatedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\SuratExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminSuratController extends Controller
{
    public function export()
    {
        return Excel::download(new SuratExport, 'rekap_surat_' . date('Y-m-d_H-i') . '.xlsx');
    }

    public function index(Request $request)
    {
        $sortFields = [
            'no_surat' => 'surats.no_surat',
            'pemohon' => 'pemohon_nama', // We will use raw select for this
            'surat_jenis_id' => 'surat_jenis.nama',
            'tanggal_surat' => 'surats.tanggal_surat',
            'status' => 'surats.status',
            'created_at' => 'surats.created_at',
        ];

        $sort = $request->input('sort', 'created_at');
        if (!array_key_exists($sort, $sortFields)) {
            $sort = 'created_at';
        }

        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = Surat::with(['jenis', 'pemohonDosen', 'pemohonMahasiswa', 'mahasiswa'])
            ->select('surats.*')
            ->selectRaw('COALESCE(m.nama, d.nama) as pemohon_nama')
            ->leftJoin('mahasiswa as m', 'm.id', '=', 'surats.pemohon_mahasiswa_id')
            ->leftJoin('dosen as d', 'd.id', '=', 'surats.pemohon_dosen_id')
            ->leftJoin('surat_jenis', 'surat_jenis.id', '=', 'surats.surat_jenis_id');

        if ($request->filled('search')) {
            $s = '%' . trim((string) $request->input('search')) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('surats.no_surat', 'like', $s)
                    ->orWhere('surats.tujuan', 'like', $s)
                    ->orWhere('surats.perihal', 'like', $s)
                    ->orWhere('surats.status', 'like', $s)
                    ->orWhere('m.nama', 'like', $s)
                    ->orWhere('d.nama', 'like', $s)
                    ->orWhere('surat_jenis.nama', 'like', $s);
            });
        }

        $query->orderBy($sortFields[$sort], $direction);

        $items = $query->paginate(20)->withQueryString();
        $jenisList = SuratJenis::orderBy('nama')->get();

        return view('admin.surat.index', compact('items', 'jenisList', 'sort', 'direction'));
    }

    /**
     * Provide next nomor surat for selected surat jenis.
     */
    public function getNextNoSurat(Request $request)
    {
        $validated = $request->validate([
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
        ]);

        $nextNoSurat = $this->generateDefaultNoSurat((int) $validated['surat_jenis_id']);

        return response()->json([
            'next_no_surat' => $nextNoSurat,
        ]);
    }

    public function create()
    {
        $jenisList = SuratJenis::where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'kode', 'form_fields']);
        
        // Only pass essential fields to prevent JavaScript truncation
        // Email is removed to reduce data size significantly
        // LIMIT records to prevent script truncation (users can search if needed)
        $dosens = Dosen::orderBy('nama')->limit(50)->get(['id', 'nama', 'nip'])->map(function($d) {
            return [
                'id' => $d->id,
                'nama' => $d->nama,
                'nip' => $d->nip
            ];
        });
        
        $mahasiswas = Mahasiswa::orderBy('nama')->limit(100)->get(['id', 'nama', 'npm'])->map(function($m) {
            return [
                'id' => $m->id,
                'nama' => $m->nama,
                'npm' => $m->npm
            ];
        });

        return view('admin.surat.create', compact('jenisList', 'dosens', 'mahasiswas'));
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
            'no_surat' => 'nullable|string|max:100',
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

                $sources = $f['pemohon_sources'] ?? ['mahasiswa', 'dosen'];
                if (!is_array($sources) || empty($sources)) {
                    $sources = ['mahasiswa', 'dosen'];
                }

                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                $rules["form_data.$key.type"] = ($required ? 'required|' : 'nullable|') . 'in:' . implode(',', $sources);
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

            if ($type === 'table') {
                $columns = is_array($f['columns'] ?? null) ? $f['columns'] : [];
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                
                // Validate each row
                $rules["form_data.$key.*"] = 'array';
                
                // Validate each column in each row
                foreach ($columns as $col) {
                    if (is_array($col) && isset($col['key'])) {
                        $colKey = $col['key'];
                        $colType = $col['type'] ?? 'text';
                        
                        // Handle column with pemohon type
                        if ($colType === 'pemohon') {
                            $sources = $col['pemohon_sources'] ?? ['mahasiswa', 'dosen'];
                            if (!is_array($sources) || empty($sources)) {
                                $sources = ['mahasiswa', 'dosen'];
                            }
                            
                            $rules["form_data.$key.*.$colKey"] = 'nullable|array';
                            $rules["form_data.$key.*.$colKey.type"] = 'nullable|in:' . implode(',', $sources);
                            $rules["form_data.$key.*.$colKey.id"] = 'nullable|integer|min:1';
                        } else {
                            $rules["form_data.$key.*.$colKey"] = 'nullable|string|max:500';
                        }
                    }
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

            // default
            $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'string';
        }

        $validated = $request->validate($rules);

        $data = is_array($validated['form_data'] ?? null) ? $validated['form_data'] : [];
        $files = is_array($validated['form_files'] ?? null) ? $validated['form_files'] : [];

        $payload = [
            'surat_jenis_id' => $jenis->id,
            'pemohon_type' => 'dosen',
            'pemohon_dosen_id' => null,
            'pemohon_mahasiswa_id' => null,
            'mahasiswa_id' => null,
            'untuk_type' => 'umum',
            'no_surat' => $this->generateDefaultNoSurat($jenis->id),
            'tanggal_surat' => now()->timezone('Asia/Jakarta')->toDateString(),
            'tujuan' => null,
            'perihal' => null,
            'isi' => null,
            'penerima_email' => null,
            'data' => [],
            'status' => 'diajukan',
        ];

        if (!empty($validated['no_surat'])) {
            $payload['no_surat'] = $this->normalizeNoSurat((string) $validated['no_surat']);
        }

        if ($pemohonFieldKey && isset($data[$pemohonFieldKey]) && is_array($data[$pemohonFieldKey])) {
            $pemType = $data[$pemohonFieldKey]['type'] ?? null;
            $pemId = (int) ($data[$pemohonFieldKey]['id'] ?? 0);
            if ($pemType === 'mahasiswa' && $pemId > 0) {
                $payload['pemohon_type'] = 'mahasiswa';
                $payload['pemohon_mahasiswa_id'] = $pemId;
            }
            if ($pemType === 'dosen' && $pemId > 0) {
                $payload['pemohon_type'] = 'dosen';
                $payload['pemohon_dosen_id'] = $pemId;
            }
        }

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

        // Handle uploads
        $stored = [];
        foreach ($files as $k => $file) {
            if (!$file) continue;
            $stored[$k] = $file->store('documents/surat/attachments', 'uploads');
        }

        // Store remaining data + uploaded file paths
        $payload['data'] = array_merge($data, $stored);

        $surat = Surat::create($payload);

        // Send notification to all admins
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->notify(new SuratSubmittedNotification($surat));
        }

        return redirect()->route('admin.surat.show', $surat)->with('success', 'Surat berhasil dibuat.');
    }

    /**
     * Generate default nomor surat that resets every year starting from 001
     */
    private function generateDefaultNoSurat(?int $suratJenisId = null): string
    {
        $currentYear = Carbon::now()->year;

        $query = Surat::whereYear('created_at', $currentYear);

        if ($suratJenisId) {
            $query->where('surat_jenis_id', $suratJenisId);
        }

        $maxNumber = $query->max(DB::raw('CAST(no_surat AS UNSIGNED)'));
        $nextNumber = $maxNumber ? ((int) $maxNumber + 1) : 1;

        return $this->normalizeNoSurat((string) $nextNumber);
    }

    private function normalizeNoSurat(string $value): string
    {
        $numeric = ltrim($value, '0');
        if ($numeric === '') {
            $numeric = '0';
        }

        return str_pad($numeric, 3, '0', STR_PAD_LEFT);
    }

    public function show(Surat $surat)
    {
        $surat->load(['jenis.template', 'pemohonDosen', 'pemohonMahasiswa', 'mahasiswa', 'jenis']);
        $dosens = Dosen::orderBy('nama')->get(['id', 'nama', 'nip', 'email']);
        $mahasiswas = Mahasiswa::orderBy('nama')->get(['id', 'nama', 'npm', 'email']);
        
        return view('admin.surat.show', compact('surat', 'dosens', 'mahasiswas'));
    }

    public function update(Request $request, Surat $surat)
    {
        $validated = $request->validate([
            'no_surat' => 'nullable|string|max:100',
            'tanggal_surat' => 'required|date',
            'tujuan' => 'nullable|string|max:255',
            'perihal' => 'nullable|string|max:255',
            'isi' => 'nullable|string',
            'penerima_email' => 'nullable|email',
            'status' => 'required|in:diajukan,diproses,dikirim,ditolak',
            'form_data' => 'nullable|array',
        ]);

        if (!empty($validated['no_surat'])) {
            $validated['no_surat'] = $this->normalizeNoSurat((string) $validated['no_surat']);
        }

        $data = $request->input('form_data', []);
        if (is_array($data)) {
            // Mapping common keys just like in store()
            foreach (['tujuan', 'perihal', 'isi', 'penerima_email', 'untuk_type', 'mahasiswa_id'] as $k) {
                if (array_key_exists($k, $data)) {
                    $validated[$k] = $data[$k];
                    unset($data[$k]);
                }
            }

            // Handle pemohon field mapping if present
            $jenis = $surat->jenis;
            $formFields = is_array($jenis?->form_fields) ? $jenis->form_fields : [];
            $pemohonFieldKey = null;
            foreach ($formFields as $f) {
                if (is_array($f) && ($f['type'] ?? '') === 'pemohon') {
                    $pemohonFieldKey = $f['key'] ?? null;
                    break;
                }
            }

            if ($pemohonFieldKey && isset($data[$pemohonFieldKey]) && is_array($data[$pemohonFieldKey])) {
                $pemType = $data[$pemohonFieldKey]['type'] ?? null;
                $pemId = (int) ($data[$pemohonFieldKey]['id'] ?? 0);
                if ($pemType === 'mahasiswa' && $pemId > 0) {
                    $validated['pemohon_type'] = 'mahasiswa';
                    $validated['pemohon_mahasiswa_id'] = $pemId;
                    $validated['pemohon_dosen_id'] = null;
                } elseif ($pemType === 'dosen' && $pemId > 0) {
                    $validated['pemohon_type'] = 'dosen';
                    $validated['pemohon_dosen_id'] = $pemId;
                    $validated['pemohon_mahasiswa_id'] = null;
                }
            }

            $validated['data'] = array_merge($surat->data ?? [], $data);
        }

        $previousStatus = $surat->status;
        
        $surat->update($validated);

        // Send notification if status changed
        if ($previousStatus !== $surat->status) {
            // Notify pemohon (dosen or mahasiswa)
            if ($surat->pemohon_type === 'dosen' && $surat->pemohonDosen) {
                $surat->pemohonDosen->notify(new SuratStatusUpdatedNotification($surat, $previousStatus));
            } elseif ($surat->pemohon_type === 'mahasiswa' && $surat->pemohonMahasiswa) {
                $surat->pemohonMahasiswa->notify(new SuratStatusUpdatedNotification($surat, $previousStatus));
            }
        }

        return redirect()->route('admin.surat.show', $surat)->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(Surat $surat)
    {
        // Clean up generated file and any uploaded attachments stored in public disk.
        $paths = [];

        if (is_string($surat->generated_file_path) && $surat->generated_file_path !== '') {
            $paths[] = $surat->generated_file_path;
        }

        $data = $surat->data;
        $stack = is_array($data) ? [$data] : [];
        while (!empty($stack)) {
            $current = array_pop($stack);
            foreach ($current as $v) {
                if (is_array($v)) {
                    $stack[] = $v;
                    continue;
                }
                if (is_string($v) && $v !== '') {
                    $paths[] = $v;
                }
            }
        }

        $paths = array_values(array_unique(array_filter($paths)));
        foreach ($paths as $p) {
            $normalized = str_replace('\\', '/', ltrim((string) $p, '/'));

            // Only allow deleting known directories
            if (Str::startsWith($normalized, ['surat-attachments/', 'documents/surat/generated/', 'documents/surat/attachments/'])) {
                try {
                    Storage::disk('uploads')->delete($normalized);
                } catch (\Throwable $e) {
                    // ignore cleanup errors
                }
            }
        }

        $surat->delete();

        return redirect()->route('admin.surat.index')->with('success', 'Permohonan surat berhasil dihapus.');
    }
}
