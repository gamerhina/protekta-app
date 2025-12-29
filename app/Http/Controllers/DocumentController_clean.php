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

// Using PHPWord TemplateProcessor instead of OpenTBS

class DocumentController extends Controller
{
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
            $baseData = $this->getSeminarData($seminar);
            $finalData = $this->buildFinalData($template, $baseData);
            $docxPath = $this->fillTemplate($template, $finalData);
            
            Log::info('DOCX generation completed with OpenTBS', [
                'template' => $template->id,
                'seminar' => $seminar->id,
                'file_path' => $docxPath,
                'file_size' => file_exists($docxPath) ? filesize($docxPath) : 'not_found'
            ]);

            $filename = $this->buildDownloadName(
                $seminar->mahasiswa?->nama ?? 'Mahasiswa',
                $template->nama ?? 'Template',
                'docx'
            );

            return response()->download($docxPath, $filename)->deleteFileAfterSend(true);
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

    /**
     * Helper methods
     */
    private function getTemplateFilePath(DocumentTemplate $template): string
    {
        return storage_path('app/' . $template->file_path);
    }

    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function normalizeAssociativeArray($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    private function buildFinalData(DocumentTemplate $template, array $baseData): array
    {
        $finalData = [];
        $availableTags = $template->available_tags;
        if (!is_array($availableTags)) {
            $availableTags = json_decode($availableTags ?? '[]', true) ?: [];
        }
        $tagMappings = $this->normalizeAssociativeArray($template->tag_mappings);

        foreach ($availableTags as $tag) {
            $sourceKey = $tagMappings[$tag] ?? $tag;
            $finalData[$tag] = $baseData[$sourceKey] ?? '';
        }

        return $finalData;
    }

    private function fillTemplate(DocumentTemplate $template, array $data): string
    {
        $templatePath = $this->getTemplateFilePath($template);
        
        if (!file_exists($templatePath)) {
            throw new \Exception('Template file not found: ' . $templatePath);
        }
        
        // Ensure temp directory exists
        $tempDir = storage_path('app/temp/documents');
        $this->ensureDirectory($tempDir);

        $fileName = uniqid('doc_', true) . '.docx';
        $outputPath = $tempDir . '/' . $fileName;
        
        try {
            // Copy template to working location
            copy($templatePath, $outputPath);
            
            // Initialize PHPWord TemplateProcessor
            $processor = new TemplateProcessor($outputPath);
            
            // Get tag mappings
            $tagMappings = $this->normalizeAssociativeArray($template->tag_mappings);
            $tagTypes = $this->normalizeAssociativeArray($template->tag_types);
            
            // Process all tags
            if ($tagMappings && is_array($tagMappings)) {
                foreach ($tagMappings as $tag => $field) {
                    $tagType = isset($template->tag_types[$tag]) ? $template->tag_types[$tag] : 'standard';
                    $value = $data[$field] ?? '';
                    
                    switch ($tagType) {
                        case 'image':
                            // For image type, prepare image path
                            if ($value && file_exists($value)) {
                                try {
                                    // Set image with proper dimensions
                                    $imageInfo = @getimagesize($value);
                                    if ($imageInfo !== false) {
                                        $processor->setImageValue($tag, [
                                            'path' => $value,
                                            'width' => min($imageInfo[0], 300),
                                            'height' => min($imageInfo[1], 150)
                                        ]);
                                    } else {
                                        $processor->setValue($tag, '');
                                    }
                                } catch (\Exception $e) {
                                    Log::warning('Failed to set image: ' . $tag . ' - ' . $e->getMessage());
                                    $processor->setValue($tag, '');
                                }
                            } else {
                                $processor->setValue($tag, '');
                            }
                            break;
                            
                        default:
                            // Standard text replacement
                            $processor->setValue($tag, $value);
                            break;
                    }
                }
            }

            // Save the processed document
            $processor->saveAs($outputPath);
            
            return $outputPath;
        } catch (\Exception $e) {
            Log::error('DOCX generation failed', [
                'template' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to generate DOCX: ' . $e->getMessage());
        }
    }

    private function buildDownloadName(string $studentName, string $templateName, string $extension): string
    {
        $student = $this->sanitizeFileSegment($studentName) ?: 'Mahasiswa';
        $template = $this->sanitizeFileSegment($templateName) ?: 'Template';
        $extension = ltrim($extension, '.');

        return trim($student . ' ' . $template) . '.' . $extension;
    }

    private function sanitizeFileSegment(string $segment): string
    {
        // Remove special characters and replace with space
        $clean = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $segment);
        
        // Remove multiple spaces
        $clean = preg_replace('/\s+/', ' ', $clean);
        
        // Trim and return
        return trim($clean);
    }

    private function getSeminarData(Seminar $seminar): array
    {
        return [
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
    }
}
