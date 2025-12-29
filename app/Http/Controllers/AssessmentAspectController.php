<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SeminarJenis;
use App\Models\AssessmentAspect;

class AssessmentAspectController extends Controller
{
    public function index(SeminarJenis $seminarJenis)
    {
        $aspects = $seminarJenis->assessmentAspects()
            ->orderBy('evaluator_type')
            ->orderBy('urutan')
            ->get()
            ->groupBy('evaluator_type');

        return view('admin.management.seminarjenis.aspects', compact('seminarJenis', 'aspects'));
    }

    public function store(Request $request, SeminarJenis $seminarJenis)
    {
        $request->validate([
            'evaluator_type' => 'required|in:p1,p2,pembahas',
            'nama_aspek' => 'required|string|max:255',
            'urutan' => 'required|integer|min:0',
        ]);

        $seminarJenis->assessmentAspects()->create($request->only(['evaluator_type', 'nama_aspek', 'urutan']));

        return redirect()->back()->with('success', 'Aspek penilaian berhasil ditambahkan!');
    }

    public function update(Request $request, SeminarJenis $seminarJenis, AssessmentAspect $aspect)
    {
        $request->validate([
            'nama_aspek' => 'required|string|max:255',
            'urutan' => 'required|integer|min:0',
        ]);

        $aspect->update($request->only(['nama_aspek', 'urutan']));

        return redirect()->back()->with('success', 'Aspek penilaian berhasil diperbarui!');
    }

    public function destroy(SeminarJenis $seminarJenis, AssessmentAspect $aspect)
    {
        $aspect->delete();

        return redirect()->back()->with('success', 'Aspek penilaian berhasil dihapus!');
    }
}
