<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use App\Models\DocumentTemplate;
use App\Models\Seminar;
use App\Models\SeminarJenis;
use App\Models\SeminarNilai;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    /**
     * Generate document and show preview
     */
    public function previewDocument($templateId, $seminarId)
    {
        try {
            $template = DocumentTemplate::findOrFail($templateId);
            $seminar = Seminar::with(['mahasiswa', 'seminarJenis', 'p1Dosen', 'p2Dosen', 'pembahasDosen'])->findOrFail($seminarId);
            
            return view('admin.document.preview', compact('template', 'seminar'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.document.templates')
                ->with('error', 'Template atau Seminar tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download generated DOCX document
     */
    public function downloadDocx(DocumentTemplate $template, Seminar $seminar)
    {
        try {
            $templatePath = storage_path('app/' . $template->file_path);
            
            if (!file_exists($templatePath)) {
                throw new \Exception('Template file not found: ' . $templatePath);
            }
            
            // Get seminar data
            $seminarData = [
                'nama' => $seminar->mahasiswa->nama ?? '',
                'npm' => $seminar->mahasiswa->npm ?? '',
                'prodi' => $seminar->mahasiswa->prodi ?? '',
                'jenis_seminar' => $seminar->seminarJenis->nama ?? '',
                'tanggal' => $seminar->tanggal ? date('d F Y', strtotime($seminar->tanggal)) : '',
                'judul' => $seminar->judul ?? '',
                'p1_nama' => $seminar->p1Dosen->nama ?? '',
                'p1_nip' => $seminar->p1Dosen->nip ?? '',
                'p2_nama' => $seminar->p2Dosen->nama ?? '',
                'p2_nip' => $seminar->p2Dosen->nip ?? '',
                'pembahas_nama' => $seminar->pembahasDosen->nama ?? '',
                'pembahas_nip' => $seminar->pembahasDosen->nip ?? '',
            ];
            
            // Create temp directory
            $tempDir = storage_path('app/temp/documents');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $fileName = uniqid('doc_', true) . '.docx';
            $outputPath = $tempDir . '/' . $fileName;
            
            // Load template
            $processor = new TemplateProcessor($templatePath);
            
            // Replace all template variables
            foreach ($seminarData as $key => $value) {
                $processor->setValue($key, $value);
            }
            
            // Save document
            $processor->saveAs($outputPath);
            
            Log::info('DOCX generation completed', [
                'template' => $template->id,
                'seminar' => $seminar->id,
                'file_path' => $outputPath,
                'file_size' => file_exists($outputPath) ? filesize($outputPath) : 'not_found'
            ]);
            
            $filename = ($seminar->mahasiswa->nama ?? 'Mahasiswa') . ' ' . ($template->nama ?? 'Template') . '.docx';
            
            return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
            
        } catch (\Throwable $e) {
            Log::error('DOCX generation failed', [
                'template' => $template->id,
                'seminar' => $seminar->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Gagal membuat DOCX: ' . $e->getMessage());
        }
    }
    /**
     * Generate a document using a template
     */
    public function generateDocument(Request $request, $templateId, $seminarId = null)
    {
        $template = DocumentTemplate::findOrFail($templateId);

        // Load template file
        $templatePath = storage_path('app/' . $template->file_path);

        if (!file_exists($templatePath)) {
            abort(404, 'Template file not found.');
        }

        $phpWord = new TemplateProcessor($templatePath);

        // If seminar ID is provided, fetch seminar data to populate template
        if ($seminarId) {
            $seminar = Seminar::with(['mahasiswa', 'p1Dosen', 'p2Dosen', 'pembahasDosen', 'seminarJenis'])->findOrFail($seminarId);

            // Map template variables with seminar data
            $data = [
                'nama' => $seminar->mahasiswa->nama ?? '',
                'npm' => $seminar->mahasiswa->npm ?? '',
                'judul' => $seminar->judul ?? '',
                'tanggal' => $seminar->tanggal ? date('d F Y', strtotime($seminar->tanggal)) : '',
                'jenis_seminar' => $seminar->seminarJenis->nama ?? '',
                'no_surat' => $seminar->no_surat ?? '',
                'p1_nama' => $seminar->p1Dosen->nama ?? '',
                'p1_nip' => $seminar->p1Dosen->nip ?? '',
                'p2_nama' => $seminar->p2Dosen->nama ?? '',
                'p2_nip' => $seminar->p2Dosen->nip ?? '',
                'pembahas_nama' => $seminar->pembahasDosen->nama ?? '',
                'pembahas_nip' => $seminar->pembahasDosen->nip ?? '',
            ];

            // Replace variables in template
            foreach ($data as $key => $value) {
                $phpWord->setValue($key, $value);
            }
        }

        // Generate output filename
        $fileName = $template->kode . '_' . date('Y-m-d') . '.docx';
        $outputPath = storage_path('app/temp/' . $fileName);

        // Create temp directory if it doesn't exist
        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        $phpWord->saveAs($outputPath);

        // Return file for download
        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    /**
     * Show document templates page for admins
     */
    public function showTemplates()
    {
        $templates = DocumentTemplate::with('seminarJenis')->get();
        return view('admin.document.templates', compact('templates'));
    }

    /**
     * Show form to create a new document template
     */
    public function showCreateTemplateForm()
    {
        $seminarJenis = SeminarJenis::all();
        return view('admin.document.create', compact('seminarJenis'));
    }

    /**
     * Store a new document template
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|unique:document_templates,kode',
            'nama' => 'required|string',
            'seminar_jenis_id' => 'required|exists:seminar_jenis,id',
            'file_path' => 'required|file|mimes:doc,docx|max:10240',
        ]);

        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('templates', $filename, 'local');
        }

        DocumentTemplate::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'seminar_jenis_id' => $request->seminar_jenis_id,
            'file_path' => $path,
            'available_tags' => '[]',
            'tag_mappings' => '{}',
            'tag_types' => '{}',
            'tag_properties' => '{}',
            'status' => 'active',
        ]);

        return redirect()->route('admin.document.templates')->with('success', 'Template berhasil dibuat!');
    }

    /**
     * Download generated DOCX document
     */
    public function downloadDocx(DocumentTemplate $template, Seminar $seminar)
    {
        try {
            $templatePath = storage_path('app/' . $template->file_path);
            
            if (!file_exists($templatePath)) {
                throw new \Exception('Template file not found: ' . $templatePath);
            }
            
            // Get seminar data
            $seminarData = [
                'nama' => $seminar->mahasiswa->nama ?? '',
                'npm' => $seminar->mahasiswa->npm ?? '',
                'prodi' => $seminar->mahasiswa->prodi ?? '',
                'jenis_seminar' => $seminar->seminarJenis->nama ?? '',
                'tanggal' => $seminar->tanggal ? date('d F Y', strtotime($seminar->tanggal)) : '',
                'judul' => $seminar->judul ?? '',
                'p1_nama' => $seminar->p1Dosen->nama ?? '',
                'p1_nip' => $seminar->p1Dosen->nip ?? '',
                'p2_nama' => $seminar->p2Dosen->nama ?? '',
                'p2_nip' => $seminar->p2Dosen->nip ?? '',
                'pembahas_nama' => $seminar->pembahasDosen->nama ?? '',
                'pembahas_nip' => $seminar->pembahasDosen->nip ?? '',
            ];
            
            // Create temp directory
            $tempDir = storage_path('app/temp/documents');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $fileName = uniqid('doc_', true) . '.docx';
            $outputPath = $tempDir . '/' . $fileName;
            
            // Load template
            $processor = new TemplateProcessor($templatePath);
            
            // Replace all template variables
            foreach ($seminarData as $key => $value) {
                $processor->setValue($key, $value);
            }
            
            // Save the document
            $processor->saveAs($outputPath);
            
            Log::info('DOCX generation completed', [
                'template' => $template->id,
                'seminar' => $seminar->id,
                'file_path' => $outputPath,
                'file_size' => file_exists($outputPath) ? filesize($outputPath) : 'not_found'
            ]);
            
            $filename = ($seminar->mahasiswa->nama ?? 'Mahasiswa') . ' ' . ($template->nama ?? 'Template') . '.docx';
            
            return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
            
        } catch (\Throwable $e) {
            Log::error('DOCX generation failed', [
                'template' => $template->id,
                'seminar' => $seminar->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Gagal membuat DOCX: ' . $e->getMessage());
        }
    }

    /**
     * Generate document and show preview
     */
    public function previewDocument($templateId, $seminarId)
    {
        try {
            $template = DocumentTemplate::findOrFail($templateId);
            $seminar = Seminar::with(['mahasiswa', 'seminarJenis', 'p1Dosen', 'p2Dosen', 'pembahasDosen'])->findOrFail($seminarId);
            
            return view('admin.document.preview', compact('template', 'seminar'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.document.templates')
                ->with('error', 'Template atau Seminar tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
