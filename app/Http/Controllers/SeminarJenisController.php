<?php

namespace App\Http\Controllers;

use App\Models\SeminarJenis;
use App\Support\PaginationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class SeminarJenisController extends Controller
{
    private function normalizeExtensions(string $value): array
    {
        $raw = preg_split('/[\s,;]+/', strtolower(trim($value))) ?: [];

        $exts = [];
        foreach ($raw as $ext) {
            $ext = ltrim(trim((string) $ext), '.');
            if ($ext === '') {
                continue;
            }
            if (! preg_match('/^[a-z0-9]+$/', $ext)) {
                continue;
            }
            $exts[] = $ext;
        }

        $exts = array_values(array_unique($exts));

        return $exts;
    }

    private function normalizeBerkasItems(array $keys, array $labels, array $extensions, array $maxSizeMb, array $requiredFlags): array
    {
        $items = [];

        $count = max(count($keys), count($labels), count($extensions), count($maxSizeMb), count($requiredFlags));
        for ($i = 0; $i < $count; $i++) {
            $key = isset($keys[$i]) ? trim((string) $keys[$i]) : '';
            $label = isset($labels[$i]) ? trim((string) $labels[$i]) : '';
            $extRaw = isset($extensions[$i]) ? trim((string) $extensions[$i]) : '';
            $maxMbRaw = $maxSizeMb[$i] ?? null;
            $required = isset($requiredFlags[$i]) ? (bool) $requiredFlags[$i] : true;

            if ($label === '' && $key === '') {
                continue;
            }

            if ($key === '') {
                $key = $label;
            }

            $key = strtolower($key);
            $key = preg_replace('/[^a-z0-9]+/', '_', $key);
            $key = trim($key, '_');
            if ($key === '') {
                continue;
            }

            $items[] = [
                'key' => $key,
                'label' => $label !== '' ? $label : strtoupper($key),
                'extensions' => $extRaw !== '' ? $this->normalizeExtensions($extRaw) : null,
                'max_size_kb' => ($maxMbRaw !== null && $maxMbRaw !== '') ? (int) round(((float) $maxMbRaw) * 1024) : null,
                'required' => $required,
            ];
        }

        $deduped = [];
        foreach ($items as $item) {
            $deduped[$item['key']] = $item;
        }

        return array_values($deduped);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = SeminarJenis::with('assessmentAspects');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($builder) use ($like) {
                $builder->where('nama', 'like', $like)
                    ->orWhere('kode', 'like', $like)
                    ->orWhere('keterangan', 'like', $like);
            });
        }

        $sortFields = [
            'nama' => 'nama',
            'kode' => 'kode',
            'updated_at' => 'updated_at',
            'created_at' => 'created_at',
        ];

        $sort = $request->input('sort', 'nama');
        if (! array_key_exists($sort, $sortFields)) {
            $sort = 'nama';
        }

        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $perPage = PaginationHelper::resolvePerPage($request);

        $seminarJenis = $query
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.management.seminarjenis.index', compact('seminarJenis', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $syaratReady = Schema::hasColumn('seminar_jenis', 'syarat_seminar');
        $berkasItemsReady = Schema::hasColumn('seminar_jenis', 'berkas_syarat_items');

        return view('admin.management.seminarjenis.create', compact('syaratReady', 'berkasItemsReady'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $syaratReady = Schema::hasColumn('seminar_jenis', 'syarat_seminar');
        $berkasItemsReady = Schema::hasColumn('seminar_jenis', 'berkas_syarat_items');

        if (! $syaratReady && $request->filled('syarat_seminar')) {
            return redirect()->back()->withInput()->with('error', 'Kolom syarat seminar belum ada di database. Jalankan: php artisan migrate');
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'kode' => 'required|alpha_dash|unique:seminar_jenis,kode',
            'keterangan' => 'nullable|string',
            'syarat_seminar' => 'nullable|string',
            'p1_required' => 'required|boolean',
            'p2_required' => 'required|boolean',
            'pembahas_required' => 'required|boolean',
            'berkas_items_key' => 'nullable|array',
            'berkas_items_key.*' => 'nullable|string|max:100',
            'berkas_items_label' => 'nullable|array',
            'berkas_items_label.*' => 'nullable|string|max:255',
            'berkas_items_extensions' => 'nullable|array',
            'berkas_items_extensions.*' => 'nullable|string|max:255',
            'berkas_items_max_size_mb' => 'nullable|array',
            'berkas_items_max_size_mb.*' => 'nullable|numeric|min:0.1|max:50',
            'berkas_items_required' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $p1Required = $request->boolean('p1_required', true);
        $p2Required = $request->boolean('p2_required', true);
        $pembahasRequired = $request->boolean('pembahas_required', true);
        if (! $p1Required && ! $p2Required && ! $pembahasRequired) {
            return redirect()->back()
                ->withErrors(['p1_required' => 'Minimal pilih 1 penilai yang wajib mengisi.'])
                ->withInput();
        }

        $items = $this->normalizeBerkasItems(
            (array) $request->input('berkas_items_key', []),
            (array) $request->input('berkas_items_label', []),
            (array) $request->input('berkas_items_extensions', []),
            (array) $request->input('berkas_items_max_size_mb', []),
            (array) $request->input('berkas_items_required', [])
        );

        if (! $berkasItemsReady && count($items) > 0) {
            return redirect()->back()->withInput()->with('error', 'Kolom upload syarat belum ada di database. Jalankan: php artisan migrate');
        }

        $payload = [
            'nama' => $request->nama,
            'kode' => $request->kode,
            'keterangan' => $request->keterangan,
            'syarat_seminar' => $syaratReady ? $request->syarat_seminar : null,
            // Defaults used elsewhere in UI
            'p1_weight' => $request->input('p1_weight', 35),
            'p2_weight' => $request->input('p2_weight', 35),
            'pembahas_weight' => $request->input('pembahas_weight', 30),
            'p1_required' => $p1Required,
            'p2_required' => $p2Required,
            'pembahas_required' => $pembahasRequired,
            'grading_scheme' => null,
        ];

        if ($berkasItemsReady) {
            $payload['berkas_syarat_items'] = count($items) ? $items : null;
        }

        SeminarJenis::create($payload);

        return redirect()->route('admin.seminarjenis.index')->with('success', 'Jenis seminar berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(SeminarJenis $seminarJenis)
    {
        $syaratReady = Schema::hasColumn('seminar_jenis', 'syarat_seminar');
        $berkasItemsReady = Schema::hasColumn('seminar_jenis', 'berkas_syarat_items');

        $aspects = $seminarJenis->assessmentAspects()
            ->orderBy('evaluator_type')
            ->orderBy('urutan')
            ->get()
            ->groupBy('evaluator_type');

        return view('admin.management.seminarjenis.edit', compact('seminarJenis', 'aspects', 'syaratReady', 'berkasItemsReady'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SeminarJenis $seminarJenis)
    {
        $syaratReady = Schema::hasColumn('seminar_jenis', 'syarat_seminar');
        $berkasItemsReady = Schema::hasColumn('seminar_jenis', 'berkas_syarat_items');

        if (! $syaratReady && $request->filled('syarat_seminar')) {
            return redirect()->back()->withInput()->with('error', 'Kolom syarat seminar belum ada di database. Jalankan: php artisan migrate');
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'kode' => 'required|alpha_dash|unique:seminar_jenis,kode,'.$seminarJenis->id,
            'keterangan' => 'nullable|string',
            'syarat_seminar' => 'nullable|string',
            'p1_required' => 'sometimes|boolean',
            'p2_required' => 'sometimes|boolean',
            'pembahas_required' => 'sometimes|boolean',
            'berkas_items_key' => 'nullable|array',
            'berkas_items_key.*' => 'nullable|string|max:100',
            'berkas_items_label' => 'nullable|array',
            'berkas_items_label.*' => 'nullable|string|max:255',
            'berkas_items_extensions' => 'nullable|array',
            'berkas_items_extensions.*' => 'nullable|string|max:255',
            'berkas_items_max_size_mb' => 'nullable|array',
            'berkas_items_max_size_mb.*' => 'nullable|numeric|min:0.1|max:50',
            'berkas_items_required' => 'nullable|array',
            'p1_weight' => 'required|numeric|min:0|max:100',
            'p2_weight' => 'required|numeric|min:0|max:100',
            'pembahas_weight' => 'required|numeric|min:0|max:100',
            'grading_scheme' => 'nullable|array',
            'grading_scheme.*.grade' => 'required|string|max:10',
            'grading_scheme.*.min' => 'required|numeric|min:0|max:100',
            'grading_scheme.*.max' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $p1Required = $request->has('p1_required') ? $request->boolean('p1_required') : (bool) ($seminarJenis->p1_required ?? true);
        $p2Required = $request->has('p2_required') ? $request->boolean('p2_required') : (bool) ($seminarJenis->p2_required ?? true);
        $pembahasRequired = $request->has('pembahas_required') ? $request->boolean('pembahas_required') : (bool) ($seminarJenis->pembahas_required ?? true);
        if (! $p1Required && ! $p2Required && ! $pembahasRequired) {
            return redirect()->back()
                ->withErrors(['p1_required' => 'Minimal pilih 1 penilai yang wajib mengisi.'])
                ->withInput();
        }

        // Validate total weight equals 100%
        $totalWeight = $request->p1_weight + $request->p2_weight + $request->pembahas_weight;
        if (abs($totalWeight - 100) > 0.01) {
            return redirect()->back()
                ->withErrors(['p1_weight' => 'Total bobot harus 100%'])
                ->withInput();
        }

        // Prepare grading scheme data
        $gradingScheme = null;
        if ($request->has('grading_scheme') && is_array($request->grading_scheme)) {
            $gradingScheme = array_values($request->grading_scheme); // Re-index array
        }

        $items = [];
        if ($request->input('form_type') === 'basic_info') {
            $items = $this->normalizeBerkasItems(
                (array) $request->input('berkas_items_key', []),
                (array) $request->input('berkas_items_label', []),
                (array) $request->input('berkas_items_extensions', []),
                (array) $request->input('berkas_items_max_size_mb', []),
                (array) $request->input('berkas_items_required', [])
            );
        } else {
            $items = is_array($seminarJenis->berkas_syarat_items) ? $seminarJenis->berkas_syarat_items : [];
        }

        if (! $berkasItemsReady && $request->input('form_type') === 'basic_info' && count($items) > 0) {
            return redirect()->back()->withInput()->with('error', 'Kolom upload syarat belum ada di database. Jalankan: php artisan migrate');
        }

        $payload = [
            'nama' => $request->nama,
            'kode' => $request->kode,
            'keterangan' => $request->keterangan,
            'syarat_seminar' => $syaratReady ? $request->syarat_seminar : ($seminarJenis->syarat_seminar ?? null),
            'p1_weight' => $request->p1_weight,
            'p2_weight' => $request->p2_weight,
            'pembahas_weight' => $request->pembahas_weight,
            'p1_required' => $p1Required,
            'p2_required' => $p2Required,
            'pembahas_required' => $pembahasRequired,
            'grading_scheme' => $gradingScheme,
        ];

        if ($berkasItemsReady) {
            $payload['berkas_syarat_items'] = count($items) ? $items : null;
        }

        $seminarJenis->update($payload);

        // Check which form was submitted
        if ($request->input('form_type') === 'basic_info') {
            // Stay on edit page for basic info updates
            return redirect()->route('admin.seminarjenis.edit', $seminarJenis->id)
                ->with('success', 'Informasi dasar berhasil diperbarui!');
        } else {
            // Return to index for weight updates
            return redirect()->route('admin.seminarjenis.index')
                ->with('success', 'Jenis seminar berhasil diperbarui!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(SeminarJenis $seminarJenis)
    {
        $seminarJenis->delete();

        return redirect()->route('admin.seminarjenis.index')
            ->with('success', 'Jenis seminar berhasil dihapus!');
    }
}
