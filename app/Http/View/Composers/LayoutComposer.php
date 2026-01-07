<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Seminar;
use App\Models\SeminarNilai;
use App\Models\SeminarSignature;
use App\Models\Surat;
use Carbon\Carbon;

class LayoutComposer
{
    public function compose(View $view)
    {
        $user = null;
        $guard = null;

        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];

        foreach ($guardPriority as $g) {
            if (Auth::guard($g)->check()) {
                $user = Auth::guard($g)->user();
                $guard = $g;
                break;
            }
        }
        
        $notifications = $this->buildNavbarNotifications($guard, $user);

        $view
            ->with('user', $user)
            ->with('guard', $guard)
            ->with('navbarNotifications', $notifications);
    }

    protected function buildNavbarNotifications(?string $guard, $user): array
    {
        if (!$user || !$guard) {
            return ['items' => [], 'count' => 0];
        }

        return match ($guard) {
            'admin' => $this->buildAdminNotifications(),
            'dosen' => $this->buildDosenNotifications($user),
            'mahasiswa' => $this->buildMahasiswaNotifications($user),
            default => ['items' => [], 'count' => 0],
        };
    }

    protected function buildMahasiswaNotifications($mahasiswa): array
    {
        $items = [];

        // Surat notifications (if mahasiswa is a pemohon)
        try {
            $surats = Surat::with('jenis')
                ->where('pemohon_type', 'mahasiswa')
                ->where('pemohon_mahasiswa_id', $mahasiswa->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($surats as $surat) {
                $jenisNama = $surat->jenis->nama ?? 'Surat';
                $noSurat = $surat->no_surat ? (' No: ' . $surat->no_surat) : '';

                if ($surat->status === 'diajukan') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_diajukan',
                        'title' => 'Permohonan surat dikirim',
                        'message' => $jenisNama . ' menunggu diproses admin.',
                        'url' => route('mahasiswa.dashboard'),
                        'level' => 'info',
                    ];
                } elseif ($surat->status === 'diproses') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_diproses',
                        'title' => 'Permohonan diproses',
                        'message' => $jenisNama . ' sedang diproses admin.' . $noSurat,
                        'url' => route('mahasiswa.dashboard'),
                        'level' => 'warning',
                    ];
                } elseif ($surat->status === 'dikirim') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_dikirim',
                        'title' => 'Surat dikirim',
                        'message' => $jenisNama . ' sudah dikirim.' . $noSurat,
                        'url' => route('mahasiswa.dashboard'),
                        'level' => 'success',
                    ];
                } elseif ($surat->status === 'ditolak') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_ditolak',
                        'title' => 'Permohonan ditolak',
                        'message' => $jenisNama . ' ditolak oleh admin.',
                        'url' => route('mahasiswa.dashboard'),
                        'level' => 'error',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // ignore notification errors
        }

        $seminars = Seminar::with(['seminarJenis', 'nilai', 'signatures'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($seminars as $seminar) {
            // 1. Pendaftaran diajukan - hanya jika status masih pending
            if ($seminar->status === 'diajukan') {
                $items[] = [
                    'key' => 'seminar_diajukan_' . $seminar->id,
                    'title' => 'Pendaftaran dikirim',
                    'message' => 'Pendaftaran seminar Anda telah dikirim dan menunggu persetujuan admin.',
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'info',
                ];
            }

            // 2. Pendaftaran disetujui - hanya jika nomor surat sudah ada
            if ($seminar->status === 'disetujui' && !empty($seminar->no_surat)) {
                $items[] = [
                    'key' => 'seminar_disetujui_' . $seminar->id,
                    'title' => 'Pendaftaran disetujui',
                    'message' => 'Pendaftaran disetujui. Nomor: ' . $seminar->no_surat,
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'success',
                ];
            }

            // 3. Jadwal ditetapkan - hanya jika ada tanggal dan lokasi
            if (in_array($seminar->status, ['disetujui', 'selesai'], true) && $seminar->tanggal && $seminar->lokasi) {
                $items[] = [
                    'key' => 'seminar_jadwal_' . $seminar->id,
                    'title' => 'Jadwal seminar',
                    'message' => $seminar->tanggal->translatedFormat('d F Y') . ', ' . $seminar->lokasi,
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'info',
                ];
            }

            // 4. Penilaian berlangsung - jika sebagian sudah ada nilai
            $expectedEvaluators = collect([
                $seminar->p1_dosen_id,
                $seminar->p2_dosen_id,
                $seminar->pembahas_dosen_id,
            ])->filter()->count();

            $filledScores = $seminar->nilai->unique('dosen_id')->count();

            if ($expectedEvaluators > 0 && $filledScores > 0 && $filledScores < $expectedEvaluators) {
                $items[] = [
                    'key' => 'seminar_penilaian_' . $seminar->id . '_' . $filledScores,
                    'title' => 'Penilaian berlangsung',
                    'message' => 'Menunggu ' . ($expectedEvaluators - $filledScores) . ' penguji lagi.',
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'warning',
                ];
            }

            // 5. Nilai selesai - jika semua nilai sudah masuk
            if ($seminar->status === 'selesai' || $seminar->nilai_sent_at) {
                $items[] = [
                    'key' => 'seminar_selesai_' . $seminar->id,
                    'title' => 'Nilai selesai',
                    'message' => 'Nilai akhir telah diterbitkan. Cek di aplikasi.',
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'success',
                ];
            }
        }

        // Hapus notifikasi duplikat berdasarkan title + message
        $items = collect($items)
            ->unique(fn ($n) => trim(($n['title'] ?? '') . '|' . ($n['message'] ?? '')))
            ->take(10) // Batasi maksimal 10 notifikasi
            ->values()
            ->all();

        return ['items' => $items, 'count' => count($items)];
    }

    protected function buildDosenNotifications($dosen): array
    {
        $items = [];

        // Surat notifications
        try {
            $surats = Surat::with('jenis')
                ->where('pemohon_type', 'dosen')
                ->where('pemohon_dosen_id', $dosen->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($surats as $surat) {
                $jenisNama = $surat->jenis->nama ?? 'Surat';
                $noSurat = $surat->no_surat ? (' No: ' . $surat->no_surat) : '';

                if ($surat->status === 'diajukan') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_diajukan',
                        'title' => 'Permohonan surat dikirim',
                        'message' => $jenisNama . ' menunggu diproses admin.',
                        'url' => route('dosen.surat.index'),
                        'level' => 'info',
                    ];
                } elseif ($surat->status === 'diproses') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_diproses',
                        'title' => 'Permohonan diproses',
                        'message' => $jenisNama . ' sedang diproses admin.' . $noSurat,
                        'url' => route('dosen.surat.index'),
                        'level' => 'warning',
                    ];
                } elseif ($surat->status === 'dikirim') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_dikirim',
                        'title' => 'Surat dikirim',
                        'message' => $jenisNama . ' sudah dikirim.' . $noSurat,
                        'url' => route('dosen.surat.index'),
                        'level' => 'success',
                    ];
                } elseif ($surat->status === 'ditolak') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_ditolak',
                        'title' => 'Permohonan ditolak',
                        'message' => $jenisNama . ' ditolak oleh admin.',
                        'url' => route('dosen.surat.index'),
                        'level' => 'error',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // ignore notification errors
        }

        $seminars = Seminar::with(['mahasiswa', 'nilai', 'signatures'])
            ->whereIn('status', ['disetujui', 'belum_lengkap', 'selesai'])
            ->where(function ($q) use ($dosen) {
                $q->where('p1_dosen_id', $dosen->id)
                    ->orWhere('p2_dosen_id', $dosen->id)
                    ->orWhere('pembahas_dosen_id', $dosen->id);
            })
            ->orderBy('tanggal')
            ->limit(10)
            ->get();

        foreach ($seminars as $seminar) {
            $roleLabel = null;
            $evaluatorType = null;

            if ($seminar->p1_dosen_id === $dosen->id) {
                $roleLabel = 'Pembimbing 1';
                $evaluatorType = 'p1';
            } elseif ($seminar->p2_dosen_id === $dosen->id) {
                $roleLabel = 'Pembimbing 2';
                $evaluatorType = 'p2';
            } elseif ($seminar->pembahas_dosen_id === $dosen->id) {
                $roleLabel = 'Pembahas';
                $evaluatorType = 'pembahas';
            }

            if (!$roleLabel) {
                continue;
            }

            $canEvaluate = in_array($seminar->status, ['disetujui', 'selesai'], true);
            $isUpcoming = $seminar->tanggal && Carbon::parse($seminar->tanggal)->isFuture();
            $isPast = $seminar->tanggal && Carbon::parse($seminar->tanggal)->isPast();

            $tanggalLabel = $seminar->tanggal instanceof Carbon
                ? $seminar->tanggal->translatedFormat('d F Y')
                : $seminar->tanggal;

            // 1. Mendapatkan tugas baru sebagai penguji
            if ($isUpcoming) {
                $items[] = [
                    'key' => 'dosen_tugas_' . $seminar->id . '_' . $evaluatorType,
                    'title' => 'Tugas penguji baru',
                    'message' => 'Anda sebagai ' . $roleLabel . ' untuk ' . ($seminar->mahasiswa->nama ?? '-') . ' (' . $tanggalLabel . ')',
                    'url' => route('dosen.evaluasi.index'),
                    'level' => 'info',
                ];
            }

            $nilai = $seminar->nilai->firstWhere('dosen_id', $dosen->id);
            $signature = $seminar->signatures
                ->where('dosen_id', $dosen->id)
                ->where('jenis_penilai', $evaluatorType)
                ->first();

            // 2. Belum mengisi nilai (jika seminar sedang berlangsung atau akan datang)
            if (($isUpcoming || $isPast) && $canEvaluate && !$nilai) {
                $items[] = [
                    'key' => 'dosen_perlu_nilai_' . $seminar->id . '_' . $evaluatorType,
                    'title' => 'Perlu nilai',
                    'message' => 'Mohon isi nilai ' . $roleLabel . ' untuk ' . ($seminar->mahasiswa->nama ?? '-'),
                    'url' => route('dosen.nilai.input', $seminar),
                    'level' => 'warning',
                ];
                continue;
            }

            // 3. Nilai sudah ada tapi belum tanda tangan digital
            if (($isUpcoming || $isPast) && $canEvaluate && $nilai && !$signature) {
                $items[] = [
                    'key' => 'dosen_perlu_ttd_' . $seminar->id . '_' . $evaluatorType,
                    'title' => 'Perlu TTD Digital',
                    'message' => 'Nilai tersimpan, lakukan tanda tangan digital.',
                    'url' => route('dosen.signature.form', ['seminarId' => $seminar->id, 'evaluatorType' => $evaluatorType]),
                    'level' => 'info',
                ];
                continue;
            }

            // 4. Sudah tanda tangan, tapi menunggu penguji lain
            $expectedEvaluators = collect([
                $seminar->p1_dosen_id,
                $seminar->p2_dosen_id,
                $seminar->pembahas_dosen_id,
            ])->filter()->count();

            $signedCount = $seminar->signatures->unique('dosen_id')->count();

            if ($canEvaluate && $signature && $expectedEvaluators > 0 && $signedCount < $expectedEvaluators) {
                $waitingCount = $expectedEvaluators - $signedCount;
                $items[] = [
                    'key' => 'dosen_menunggu_penguji_' . $seminar->id . '_' . $waitingCount,
                    'title' => 'Menunggu penguji',
                    'message' => 'Tanda tangan digital OK. Menunggu ' . $waitingCount . ' penguji lagi.',
                    'url' => route('dosen.evaluasi.index'),
                    'level' => 'info',
                ];
            }
        }

        // Hapus notifikasi duplikat dan batasi maksimal
        $items = collect($items)
            ->unique(fn ($n) => trim(($n['title'] ?? '') . '|' . ($n['message'] ?? '')))
            ->take(15) // Batasi maksimal 15 notifikasi
            ->values()
            ->all();

        return ['items' => $items, 'count' => count($items)];
    }

    protected function buildAdminNotifications(): array
    {
        $items = [];

        // 1. Pendaftaran baru yang perlu approval (prioritas tertinggi)
        $pendingCount = Seminar::where('status', 'diajukan')->count();
            
        if ($pendingCount > 0) {
            $latestRegistrations = Seminar::with('mahasiswa')
                ->where('status', 'diajukan')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $mahasiswaNames = $latestRegistrations->pluck('mahasiswa.nama')->filter()->take(3)->implode(', ');
            if ($pendingCount > 3) {
                $mahasiswaNames .= ' dan ' . ($pendingCount - 3) . ' lainnya';
            }
            
            $items[] = [
                'key' => 'admin_pending_reg_' . $pendingCount,
                'title' => 'Pendaftaran Baru',
                'message' => $pendingCount . ' pendaftaran menunggu approval dari ' . $mahasiswaNames,
                'url' => '/admin/seminars?filter=diajukan',
                'level' => 'warning',
            ];
        }

        // Surat: permohonan baru yang perlu diproses
        $pendingSuratCount = Surat::where('status', 'diajukan')->count();

        if ($pendingSuratCount > 0) {
            $latestSurats = Surat::with(['jenis', 'pemohonDosen', 'pemohonMahasiswa'])
                ->where('status', 'diajukan')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $pemohonNames = $latestSurats
                ->map(function ($s) {
                    return $s->pemohon_type === 'mahasiswa'
                        ? ($s->pemohonMahasiswa->nama ?? null)
                        : ($s->pemohonDosen->nama ?? null);
                })
                ->filter()
                ->take(3)
                ->implode(', ');

            if ($pendingSuratCount > 3) {
                $pemohonNames .= ' dan ' . ($pendingSuratCount - 3) . ' lainnya';
            }

            $items[] = [
                'key' => 'admin_pending_surat_' . $pendingSuratCount,
                'title' => 'Permohonan Surat',
                'message' => $pendingSuratCount . ' permohonan menunggu diproses dari ' . ($pemohonNames ?: 'pemohon'),
                'url' => route('admin.surat.index', ['status' => 'diajukan']),
                'level' => 'warning',
            ];
        }

        // 2. Seminar yang sudah disetujui tapi belum ada jadwal
        $approvedWithoutSchedule = Seminar::where('status', 'disetujui')
            ->where(function ($q) {
                $q->whereNull('tanggal')
                    ->orWhereNull('lokasi');
            })
            ->count();

        if ($approvedWithoutSchedule > 0) {
            $items[] = [
                'key' => 'admin_perlu_jadwal_' . $approvedWithoutSchedule,
                'title' => 'Perlu Jadwal',
                'message' => $approvedWithoutSchedule . ' seminar disetujui tapi belum ada jadwal.',
                'url' => '/admin/seminars?filter=disetujui',
                'level' => 'info',
            ];
        }

        // 3. Seminar siap untuk penilaian akhir
        $readyForFinalGrade = Seminar::where('status', 'disetujui')
            ->whereNotNull('tanggal')
            ->whereNotNull('lokasi')
            ->whereNull('nilai_sent_at')
            ->whereHas('nilai')
            ->count();

        if ($readyForFinalGrade > 0) {
            $items[] = [
                'key' => 'admin_siap_nilai_' . $readyForFinalGrade,
                'title' => 'Siap Kirim Nilai',
                'message' => $readyForFinalGrade . ' seminar siap dikirim nilai akhir.',
                'url' => '/admin/seminars?filter=ready',
                'level' => 'success',
            ];
        }

        // 4. Seminar yang sudah selesai
        $completedSeminars = Seminar::where('status', 'selesai')->count();
        if ($completedSeminars > 0) {
            $items[] = [
                'key' => 'admin_seminar_selesai_' . $completedSeminars,
                'title' => 'Seminar Selesai',
                'message' => $completedSeminars . ' seminar sudah selesai dinilai.',
                'url' => '/admin/seminars?filter=selesai',
                'level' => 'success',
            ];
        }

        // 5. Statistik overview untuk admin
        $totalSeminars = Seminar::count();
        if ($totalSeminars > 0) {
            $approvedPercentage = round(($completedSeminars / $totalSeminars) * 100);
            $items[] = [
                'key' => 'admin_statistik_' . $totalSeminars . '_' . $completedSeminars,
                'title' => 'Statistik Seminar',
                'message' => $completedSeminars . '/' . $totalSeminars . ' selesai (' . $approvedPercentage . '%)',
                'url' => '/admin/dashboard',
                'level' => 'info',
            ];
        }

        // Hapus notifikasi duplikat dan batasi maksimal
        $items = collect($items)
            ->unique(fn ($n) => trim(($n['title'] ?? '') . '|' . ($n['message'] ?? '')))
            ->take(12) // Batasi maksimal 12 notifikasi
            ->values()
            ->all();

        return ['items' => $items, 'count' => count($items)];
    }
}
