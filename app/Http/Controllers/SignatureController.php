<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SeminarSignature;
use App\Models\Seminar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    /**
     * Show the signature canvas for a specific seminar and evaluator type
     */
    public function showSignatureForm($seminarId, $evaluatorType)
    {
        $seminar = Seminar::with(['p1Dosen', 'p2Dosen', 'pembahasDosen'])->findOrFail($seminarId);

        if (!in_array($seminar->status, ['disetujui', 'belum_lengkap', 'selesai'], true)) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Seminar ini belum disetujui sehingga belum dapat ditandatangani.');
        }

        // Check if the authenticated dosen is the one who should sign
        $isEvaluator = false;
        $evaluatorName = '';

        if (Auth::guard('dosen')->check()) {
            $dosen = Auth::guard('dosen')->user();

            if ($evaluatorType === 'p1' && $dosen->id == $seminar->p1_dosen_id) {
                $isEvaluator = true;
                $evaluatorName = $seminar->p1Dosen->nama;
            } elseif ($evaluatorType === 'p2' && $dosen->id == $seminar->p2_dosen_id) {
                $isEvaluator = true;
                $evaluatorName = $seminar->p2Dosen->nama;
            } elseif ($evaluatorType === 'pembahas' && $dosen->id == $seminar->pembahas_dosen_id) {
                $isEvaluator = true;
                $evaluatorName = $seminar->pembahasDosen->nama;
            }
        }

        if (!$isEvaluator) {
            abort(403, 'Unauthorized to sign this document');
        }

        return view('dosen.signature.create', compact('seminar', 'evaluatorType', 'evaluatorName'));
    }

    /**
     * Store the signature
     */
    public function storeSignature(Request $request, $seminarId, $evaluatorType)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $seminar = Seminar::findOrFail($seminarId);

        if (!in_array($seminar->status, ['disetujui', 'belum_lengkap', 'selesai'], true)) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Seminar ini belum disetujui sehingga belum dapat ditandatangani.');
        }

        // Check if the authenticated dosen is the one who should sign
        $dosen = Auth::guard('dosen')->user();
        $isEvaluator = false;

        if ($evaluatorType === 'p1' && $dosen->id == $seminar->p1_dosen_id) {
            $isEvaluator = true;
        } elseif ($evaluatorType === 'p2' && $dosen->id == $seminar->p2_dosen_id) {
            $isEvaluator = true;
        } elseif ($evaluatorType === 'pembahas' && $dosen->id == $seminar->pembahas_dosen_id) {
            $isEvaluator = true;
        }

        if (!$isEvaluator) {
            abort(403, 'Unauthorized to sign this document');
        }

        // Process base64 image similar to NilaiController so admin can use seminar.files.show route
        $signatureImage = (string) $request->signature;

        // If this is a data URL, normalize and store it as a PNG file in the public disk
        if (str_starts_with($signatureImage, 'data:image/')) {
            $signatureImage = preg_replace('#^data:image/[^;]+;base64,#i', '', $signatureImage);
            $signatureImage = str_replace(' ', '+', $signatureImage);

            $signatureFileName = 'signatures/seminar-' . $seminar->id . '-' . $evaluatorType . '-' . time() . '.png';
            Storage::disk('public')->put($signatureFileName, base64_decode($signatureImage));
        } else {
            // Assume already a stored path (fallback for older data or future changes)
            $signatureFileName = $signatureImage;
        }

        // Check if signature already exists for this seminar + dosen + jenis_penilai
        $existingSignature = $seminar->signatures()
            ->where('dosen_id', $dosen->id)
            ->where('jenis_penilai', $evaluatorType)
            ->first();

        if ($existingSignature) {
            // Delete old file if we are switching to a new stored file path
            if ($existingSignature->tanda_tangan && $existingSignature->tanda_tangan !== $signatureFileName) {
                Storage::disk('public')->delete($existingSignature->tanda_tangan);
            }

            $existingSignature->update([
                'tanda_tangan' => $signatureFileName,
                'tanggal_ttd' => now(),
            ]);
        } else {
            SeminarSignature::create([
                'seminar_id' => $seminar->id,
                'dosen_id' => $dosen->id,
                'jenis_penilai' => $evaluatorType,
                'tanda_tangan' => $signatureFileName,
                'tanggal_ttd' => now(),
            ]);
        }

        // Update seminar status based on evaluator completion
        $seminar->refreshCompletionStatus();

        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan!');
    }

    /**
     * Get signature for a specific seminar and evaluator
     */
    public function getSignature($seminarId, $evaluatorType)
    {
        $signature = SeminarSignature::where('seminar_id', $seminarId)
            ->where('jenis_penilai', $evaluatorType)
            ->with('dosen')
            ->first();

        if (!$signature) {
            return response()->json(['signature' => null]);
        }

        return response()->json([
            'signature' => $signature->tanda_tangan,
            'dosen_name' => $signature->dosen->nama ?? 'Unknown',
            'date' => $signature->tanggal_ttd ? $signature->tanggal_ttd->format('d M Y') : null
        ]);
    }
}
