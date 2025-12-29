<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Seminar;
use App\Models\SeminarJenis;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SeminarRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();
        $search = trim($request->input('search', ''));
        $perPage = \App\Support\PaginationHelper::resolvePerPage($request, 10);

        $sortFields = [
            'tanggal' => 'tanggal',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        $defaultSort = 'tanggal';
        $defaultDirection = 'desc';

        $sort = $request->input('sort', $defaultSort);
        if (!array_key_exists($sort, $sortFields)) {
            $sort = $defaultSort;
        }

        $direction = strtolower($request->input('direction', $defaultDirection)) === 'asc' ? 'asc' : 'desc';

        $seminarQuery = Seminar::with(['seminarJenis', 'nilai'])
            ->where('mahasiswa_id', optional($mahasiswa)->id);

        if ($search !== '') {
            $like = "%{$search}%";
            $seminarQuery->where(function ($query) use ($like) {
                $query->where('judul', 'like', $like)
                    ->orWhereHas('seminarJenis', function ($q) use ($like) {
                        $q->where('nama', 'like', $like);
                    })
                    ->orWhere('status', 'like', $like);
            });
        }

        $items = $seminarQuery
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('mahasiswa.seminar.index', compact('items', 'defaultSort', 'defaultDirection', 'perPage'));
    }

    private function getJenisFromRequest(Request $request): ?SeminarJenis
    {
        if (! $request->filled('seminar_jenis_id')) {
            return null;
        }

        return SeminarJenis::find((int) $request->input('seminar_jenis_id'));
    }

    private function normalizeJenisItems(?SeminarJenis $jenis): array
    {
        $items = $jenis?->berkas_syarat_items;
        if (! is_array($items) || count($items) === 0) {
            return [];
        }

        $out = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $key = strtolower(trim((string) ($item['key'] ?? '')));
            $key = preg_replace('/[^a-z0-9_]+/', '_', $key);
            $key = trim($key, '_');
            $label = trim((string) ($item['label'] ?? ''));
            $required = array_key_exists('required', $item) ? (bool) $item['required'] : true;
            $extensions = $item['extensions'] ?? null;
            $maxSizeKb = $item['max_size_kb'] ?? null;

            if ($key === '' || $label === '') {
                continue;
            }

            $exts = null;
            if (is_array($extensions)) {
                $exts = array_values(array_filter(array_map(fn ($e) => ltrim(strtolower(trim((string) $e)), '.'), $extensions)));
                $exts = count($exts) ? array_values(array_unique($exts)) : null;
            }

            $maxKb = null;
            if ($maxSizeKb !== null && $maxSizeKb !== '') {
                $maxKb = (int) $maxSizeKb;
                if ($maxKb < 1) {
                    $maxKb = null;
                }
            }

            $out[] = ['key' => $key, 'label' => $label, 'required' => $required, 'extensions' => $exts, 'max_size_kb' => $maxKb];
        }

        return $out;
    }

    private function buildBerkasItemsRules(SeminarJenis $jenis): array
    {
        $items = $this->normalizeJenisItems($jenis);
        if (count($items) === 0) {
            return [];
        }

        $defaultExtensions = ['pdf'];
        $defaultMaxKb = 5120;

        $rules = [
            'berkas_syarat_items' => 'required|array',
        ];

        foreach ($items as $item) {
            $key = $item['key'];
            $required = $item['required'] === true;

            $extensions = is_array($item['extensions']) && count($item['extensions'])
                ? $item['extensions']
                : $defaultExtensions;
            $mimes = implode(',', $extensions);

            $maxKb = (int) ($item['max_size_kb'] ?? $defaultMaxKb);
            if ($maxKb < 1) {
                $maxKb = $defaultMaxKb;
            }

            $rules["berkas_syarat_items.{$key}"] = ($required ? 'required' : 'nullable')."|file|mimes:{$mimes}|max:{$maxKb}";
        }

        return $rules;
    }

    /**
     * Show the form for seminar registration
     */
    public function showRegistrationForm()
    {
        $seminarJenis = SeminarJenis::all();
        $dosens = Dosen::all();

        return view('mahasiswa.seminar.register', compact('seminarJenis', 'dosens'));
    }

    /**
     * Store a newly created seminar registration
     */
    public function store(Request $request)
    {
        $jenis = $this->getJenisFromRequest($request);

        $p1Required = (bool) ($jenis?->p1_required ?? true);
        $p2Required = (bool) ($jenis?->p2_required ?? true);
        $pembahasRequired = (bool) ($jenis?->pembahas_required ?? true);

        $itemsRules = $jenis ? $this->buildBerkasItemsRules($jenis) : [];
        $berkasRules = count($itemsRules) ? $itemsRules : [];

        $request->validate([
            'seminar_jenis_id' => [
                'required',
                'exists:seminar_jenis,id',
                Rule::unique('seminars', 'seminar_jenis_id')->where(function ($query) {
                    return $query->where('mahasiswa_id', Auth::guard('mahasiswa')->id());
                }),
            ],
            'no_surat' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('seminars', 'no_surat')->where(function ($query) use ($request) {
                    return $query->where('seminar_jenis_id', $request->seminar_jenis_id);
                }),
            ],
            'judul' => 'required|string|max:255',
            'tanggal' => 'required|date|after_or_equal:today',
            'waktu' => 'required|date_format:H:i',
            'lokasi' => 'required|string|max:255',
            'p1_dosen_id' => ($p1Required ? 'required' : 'nullable').'|exists:dosen,id',
            'p2_dosen_id' => ($p2Required ? 'required' : 'nullable').'|exists:dosen,id|different:p1_dosen_id',
            'pembahas_dosen_id' => ($pembahasRequired ? 'required' : 'nullable').'|exists:dosen,id|different:p1_dosen_id|different:p2_dosen_id',
        ] + $berkasRules, [
            'seminar_jenis_id.unique' => 'Anda sudah pernah mengajukan seminar untuk jenis ini, jika ada kesalahan silakan hubungi admin.',
        ]);

        $seminar = new Seminar;
        $seminar->mahasiswa_id = Auth::guard('mahasiswa')->id();
        $seminar->seminar_jenis_id = $request->seminar_jenis_id;
        $seminar->no_surat = $request->filled('no_surat')
            ? $this->normalizeNoSurat($request->input('no_surat'))
            : $this->generateDefaultNoSurat((int) $request->seminar_jenis_id);
        $seminar->judul = $request->judul;
        $seminar->tanggal = $request->tanggal;
        $seminar->waktu_mulai = $request->waktu; // Store the time
        $seminar->lokasi = $request->lokasi;
        $seminar->p1_dosen_id = $request->input('p1_dosen_id') ?: null;
        $seminar->p2_dosen_id = $request->input('p2_dosen_id') ?: null;
        $seminar->pembahas_dosen_id = $request->input('pembahas_dosen_id') ?: null;
        $seminar->status = 'diajukan'; // Default status

        // Handle file uploads
        if ($jenis && count($this->normalizeJenisItems($jenis)) > 0) {
            $stored = [];
            $items = $this->normalizeJenisItems($jenis);
            foreach ($items as $item) {
                $key = $item['key'];
                if ($request->hasFile("berkas_syarat_items.{$key}")) {
                    $file = $request->file("berkas_syarat_items.{$key}");
                    $stored[$key] = $file->store('seminar-berkas', 'public');
                }
            }
            $seminar->berkas_syarat = $stored;
        } else {
            $seminar->berkas_syarat = [];
        }

        $seminar->save();

        // Send notification to admins about new registration
        try {
            \Notification::route('mail', \App\Models\Admin::pluck('email')->toArray())
                ->notify(new \App\Notifications\NewSeminarRegistrationNotification($seminar));
        } catch (\Exception $e) {
            // Log error but don't fail the registration
            \Log::error('Failed to send admin notification for seminar registration: '.$e->getMessage());
        }

        return redirect()->route('mahasiswa.dashboard')->with('success', 'Pendaftaran seminar berhasil diajukan!');
    }

    /**
     * Generate default nomor surat that resets every year starting from 001.
     */
    private function generateDefaultNoSurat(?int $seminarJenisId = null): string
    {
        $currentYear = Carbon::now()->year;

        $query = Seminar::whereYear('created_at', $currentYear);
        if ($seminarJenisId) {
            $query->where('seminar_jenis_id', $seminarJenisId);
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

    /**
     * Show the specified seminar registration details
     */
    public function show(Seminar $seminar)
    {
        // Only allow viewing if the seminar belongs to current mahasiswa
        if ($seminar->mahasiswa_id !== Auth::guard('mahasiswa')->id()) {
            abort(403);
        }

        $seminar->load(['seminarJenis', 'p1Dosen', 'p2Dosen', 'pembahasDosen', 'nilai.dosen', 'nilai.assessmentScores.assessmentAspect', 'signatures']);

        return view('mahasiswa.seminar.show', compact('seminar'));
    }

    /**
     * Display stored seminar files (signatures and berkas) for the authenticated student.
     */
    public function showFile($path)
    {
        $decodedPath = rawurldecode($path);
        $normalizedPath = ltrim($decodedPath, '/');

        if (Str::contains($normalizedPath, '..')) {
            abort(403);
        }

        // Security check: ensure the file belongs to a seminar owned by this student
        // This is a bit tricky since we only have the path. 
        // For berkas_syarat, the path is stored in the json column.
        
        $mahasiswaId = Auth::guard('mahasiswa')->id();
        
        // Find if any seminar belonging to this student has this path in its berkas_syarat
        $hasAccess = Seminar::where('mahasiswa_id', $mahasiswaId)
            ->where(function($query) use ($normalizedPath) {
                $query->where('berkas_syarat', 'like', '%' . $normalizedPath . '%');
            })->exists();

        if (!$hasAccess) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($normalizedPath)) {
            abort(404);
        }

        $absolutePath = Storage::disk('public')->path($normalizedPath);

        if (ob_get_length()) {
            ob_end_clean();
        }

        return response()->file($absolutePath, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Show the form to edit a seminar registration
     */
    public function edit(Seminar $seminar)
    {
        // Only allow editing if the seminar belongs to current mahasiswa and is in 'diajukan' status
        if ($seminar->mahasiswa_id !== Auth::guard('mahasiswa')->id() || $seminar->status !== 'diajukan') {
            abort(403);
        }

        $seminarJenis = SeminarJenis::all();
        $dosens = Dosen::all();

        return view('mahasiswa.seminar.edit', compact('seminar', 'seminarJenis', 'dosens'));
    }

    /**
     * Update the specified seminar registration
     */
    public function update(Request $request, Seminar $seminar)
    {
        // Only allow updating if the seminar belongs to current mahasiswa and is in 'diajukan' status
        if ($seminar->mahasiswa_id !== Auth::guard('mahasiswa')->id() || $seminar->status !== 'diajukan') {
            abort(403);
        }

        $jenis = SeminarJenis::find((int) $seminar->seminar_jenis_id);
        $itemsRules = $jenis ? $this->buildBerkasItemsRules($jenis) : [];

        $p1Required = (bool) ($jenis?->p1_required ?? true);
        $p2Required = (bool) ($jenis?->p2_required ?? true);
        $pembahasRequired = (bool) ($jenis?->pembahas_required ?? true);

        $baseRules = [
            'judul' => 'required|string|max:255',
            'tanggal' => 'required|date|after_or_equal:today',
            'waktu' => 'required|date_format:H:i',
            'lokasi' => 'required|string|max:255',
            'p1_dosen_id' => ($p1Required ? 'required' : 'nullable').'|exists:dosen,id',
            'p2_dosen_id' => ($p2Required ? 'required' : 'nullable').'|exists:dosen,id|different:p1_dosen_id',
            'pembahas_dosen_id' => ($pembahasRequired ? 'required' : 'nullable').'|exists:dosen,id|different:p1_dosen_id|different:p2_dosen_id',
        ];

        if (count($itemsRules)) {
            $request->validate($baseRules + $itemsRules);
        } else {
            $request->validate($baseRules);
        }

        $seminar->judul = $request->judul;
        $seminar->tanggal = $request->tanggal;
        $seminar->waktu_mulai = $request->waktu; // Add time field
        $seminar->lokasi = $request->lokasi;
        $seminar->p1_dosen_id = $request->input('p1_dosen_id') ?: null;
        $seminar->p2_dosen_id = $request->input('p2_dosen_id') ?: null;
        $seminar->pembahas_dosen_id = $request->input('pembahas_dosen_id') ?: null;

        // Handle file uploads if provided
        if ($jenis && count($this->normalizeJenisItems($jenis)) > 0) {
            $existing = is_array($seminar->berkas_syarat) ? $seminar->berkas_syarat : [];
            $stored = is_array($existing) ? $existing : [];

            foreach ($this->normalizeJenisItems($jenis) as $item) {
                $key = $item['key'];
                if ($request->hasFile("berkas_syarat_items.{$key}")) {
                    $file = $request->file("berkas_syarat_items.{$key}");
                    $stored[$key] = $file->store('seminar-berkas', 'public');
                }
            }

            $seminar->berkas_syarat = $stored;
        }

        $seminar->save();

        return redirect()->route('mahasiswa.dashboard')->with('success', 'Pendaftaran seminar berhasil diperbarui!');
    }

    /**
     * Cancel a seminar registration
     */
    public function cancel(Seminar $seminar)
    {
        // Only allow canceling if the seminar belongs to current mahasiswa and is in 'diajukan' status
        if ($seminar->mahasiswa_id !== Auth::guard('mahasiswa')->id() || $seminar->status !== 'diajukan') {
            abort(403);
        }

        $seminar->status = 'ditolak'; // Set to 'ditolak' to effectively cancel
        $seminar->save();

        return redirect()->route('mahasiswa.dashboard')->with('success', 'Pendaftaran seminar berhasil dibatalkan!');
    }
}
