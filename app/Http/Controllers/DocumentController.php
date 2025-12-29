<?php

namespace App\Http\Controllers;

use App\Helpers\Terbilang;
use App\Models\DocumentTemplate;
use App\Models\Seminar;
use App\Models\SeminarJenis;
use App\Models\SeminarNilai;
use App\Support\PaginationHelper;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;
use ReflectionProperty;

class DocumentController extends Controller
{
    const PREVIEW_RELATIVE_PATH = 'storage/previews';

    private array $templatePropertyCache = [];

    /**
     * List document templates.
     */
    public function showTemplates(Request $request)
    {
        $perPage = PaginationHelper::resolvePerPage($request, 15);
        $templates = DocumentTemplate::with('seminarJenis')->paginate($perPage)->withQueryString();

        return view('admin.document.templates', compact('templates', 'perPage'));
    }

    /**
     * Show create form.
     */
    public function showCreateTemplateForm()
    {
        $seminarJenis = SeminarJenis::all();

        return view('admin.document.create', compact('seminarJenis'));
    }

    /**
     * Store new template and extract tags.
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:document_templates,kode',
            'seminar_jenis_id' => 'nullable|exists:seminar_jenis,id',
            'file' => 'required|file|mimes:docx|max:10240',
            'keterangan' => 'nullable|string',
            'email_subject_template' => 'nullable|string|max:255',
            'email_body_template' => 'nullable|string',
        ]);

        $templateDir = storage_path('app/private/document-templates');
        $this->ensureDirectory($templateDir);

        $filename = uniqid('template_', true).'.docx';
        $request->file('file')->move($templateDir, $filename);
        $fullPath = $templateDir.'/'.$filename;

        $availableTags = DocumentTemplate::extractTagsFromDocx($fullPath);

        $template = DocumentTemplate::create([
            'nama' => $request->nama,
            'kode' => $request->kode,
            'seminar_jenis_id' => $request->seminar_jenis_id,
            'file_path' => 'document-templates/'.$filename,
            'keterangan' => $request->keterangan,
            'available_tags' => $availableTags,
            'tag_mappings' => null,
            'tag_types' => null,
            'tag_properties' => null,
            'email_subject_template' => $this->resolveEmailSubjectTemplate($request->input('email_subject_template')),
            'email_body_template' => $this->resolveEmailBodyTemplate($request->input('email_body_template')),
            'aktif' => true,
        ]);

        return redirect()->route('admin.document.edit', $template->id)
            ->with('success', 'Template berhasil diunggah. Silakan lengkapi mapping tag.');
    }

    public function showEditTemplate($id)
    {
        $template = DocumentTemplate::with('seminarJenis')->findOrFail($id);
        $seminarJenis = SeminarJenis::all();
        $availableFields = DocumentTemplate::getAvailableFields();

        $templatePath = $this->getTemplateFilePath($template);
        if (! file_exists($templatePath)) {
            session()->flash('warning', 'File DOCX tidak ditemukan. Unggah ulang template.');
        }

        return view('admin.document.edit', compact('template', 'seminarJenis', 'availableFields'));
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'seminar_jenis_id' => 'nullable|exists:seminar_jenis,id',
            'keterangan' => 'nullable|string',
            'available_tags' => 'nullable|array',
            'tag_mappings' => 'nullable|array',
            'tag_types' => 'nullable|array',
            'tag_properties' => 'nullable|array',
            'new_file' => 'nullable|file|mimes:docx|max:10240',
            'aktif' => 'nullable|boolean',
            'email_subject_template' => 'nullable|string|max:255',
            'email_body_template' => 'nullable|string',
        ]);

        $data = [
            'nama' => $request->nama,
            'seminar_jenis_id' => $request->seminar_jenis_id,
            'keterangan' => $request->keterangan,
            'available_tags' => $request->available_tags ?? $template->available_tags,
            'tag_mappings' => $request->tag_mappings,
            'tag_types' => $request->tag_types,
            'tag_properties' => $request->tag_properties,
            'aktif' => $request->boolean('aktif'),
            'email_subject_template' => $this->resolveEmailSubjectTemplate($request->input('email_subject_template')),
            'email_body_template' => $this->resolveEmailBodyTemplate($request->input('email_body_template')),
        ];

        if ($request->hasFile('new_file')) {
            $templateDir = storage_path('app/private/document-templates');
            $this->ensureDirectory($templateDir);

            $existingPath = $this->getTemplateFilePath($template);
            if (file_exists($existingPath)) {
                @unlink($existingPath);
            }

            $filename = uniqid('template_', true).'.docx';
            $request->file('new_file')->move($templateDir, $filename);
            $fullPath = $templateDir.'/'.$filename;

            $data['file_path'] = 'document-templates/'.$filename;
            $data['available_tags'] = DocumentTemplate::extractTagsFromDocx($fullPath);
        }

        $template->update($data);

        return redirect()->route('admin.document.edit', $template->id)
            ->with('success', 'Template berhasil diperbarui.');
    }

    public function deleteTemplate($id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $templatePath = $this->getTemplateFilePath($template);
        if (file_exists($templatePath)) {
            @unlink($templatePath);
        }
        $template->delete();

        return redirect()->route('admin.document.templates')->with('success', 'Template berhasil dihapus.');
    }

    public function reExtractTags($id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $templatePath = $this->getTemplateFilePath($template);

        if (! file_exists($templatePath)) {
            return redirect()->back()->with('error', 'File template tidak ditemukan.');
        }

        $availableTags = DocumentTemplate::extractTagsFromDocx($templatePath);
        $existingMappings = $this->normalizeAssociativeArray($template->tag_mappings);
        $filteredMappings = array_intersect_key($existingMappings, array_flip($availableTags));

        $template->update([
            'available_tags' => $availableTags,
            'tag_mappings' => $filteredMappings,
        ]);

        return redirect()->back()->with('success', 'Tag berhasil diekstrak ulang.');
    }

    public function getSeminarsList(Request $request)
    {
        $search = $request->get('search');
        $query = Seminar::with(['mahasiswa', 'seminarJenis'])->latest();

        if ($search) {
            $query->where(function ($builder) use ($search) {
                $like = '%'.$search.'%';
                $builder->where('judul', 'like', $like)
                    ->orWhereHas('mahasiswa', function ($sub) use ($like) {
                        $sub->where('nama', 'like', $like)
                            ->orWhere('npm', 'like', $like);
                    });
            });
        }

        $seminars = $query->limit(50)->get()->map(function (Seminar $seminar) {
            return [
                'id' => $seminar->id,
                'mahasiswa_nama' => $seminar->mahasiswa?->nama ?? '-',
                'npm' => $seminar->mahasiswa?->npm ?? '-',
                'jenis_seminar' => $seminar->seminarJenis?->nama ?? '-',
                'judul' => $seminar->judul ?? '',
            ];
        });

        return response()->json(['seminars' => $seminars]);
    }

    public function previewDocument(DocumentTemplate $template, Seminar $seminar)
    {
        $templatePath = $this->getTemplateFilePath($template);
        if (! file_exists($templatePath)) {
            return redirect()->route('admin.document.templates')->with('error', 'File template tidak ditemukan.');
        }

        $baseData = $this->getSeminarData($seminar);
        $finalData = $this->buildFinalData($template, $baseData);
        $emailDefaults = $this->buildEmailDefaults($template, $seminar, $baseData, $finalData);

        $availableTags = $template->available_tags;
        if (! is_array($availableTags)) {
            $availableTags = json_decode($availableTags ?? '[]', true) ?: [];
        }

        $tagTypes = $this->normalizeAssociativeArray($template->tag_types ?? []);

        return view('admin.document.preview', [
            'template' => $template,
            'seminar' => $seminar,
            'emailDefaults' => $emailDefaults,
            'finalData' => $finalData,
            'availableTags' => $availableTags,
            'tagTypes' => $tagTypes,
        ]);
    }

    public function downloadDocx(DocumentTemplate $template, Seminar $seminar)
    {
        try {
            $baseData = $this->getSeminarData($seminar);
            $finalData = $this->buildFinalData($template, $baseData);

            $docxPath = $this->generateDocxFromTemplate($template, $finalData);

            Log::info('DOCX generation completed (TemplateProcessor)', [
                'template' => $template->id,
                'seminar' => $seminar->id,
                'file_path' => $docxPath,
                'file_size' => file_exists($docxPath) ? filesize($docxPath) : 'not_found',
            ]);

            $rawName = ($seminar->mahasiswa?->nama ?? 'Mahasiswa') . ' - ' . ($template->nama ?? 'Template');
            $safeName = preg_replace('/[<>:"\/\\|?*]/', '', $rawName);
            $filename = trim($safeName) . '.docx';

            return response()->download($docxPath, $filename)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('DOCX generation failed (TemplateProcessor)', [
                'template' => $template->id,
                'seminar' => $seminar->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Gagal membuat DOCX: '.$e->getMessage());
        }
    }

    public function generatePreviewDocx(DocumentTemplate $template, Seminar $seminar)
    {
        $docxPath = null;

        try {
            $baseData = $this->getSeminarData($seminar);
            $finalData = $this->buildFinalData($template, $baseData);

            $overrides = $this->normalizeAssociativeArray(request()->input('overrides', []));
            foreach ($overrides as $tag => $value) {
                if (! is_string($tag)) {
                    continue;
                }
                $finalData[$tag] = is_scalar($value) ? (string) $value : '';
            }

            $docxPath = $this->generateDocxFromTemplate($template, $finalData);

            $previewDir = $this->getPreviewStorageDirectory();
            $token = uniqid('preview_', true).'.docx';
            $publicPath = $previewDir.DIRECTORY_SEPARATOR.$token;

            if (! @copy($docxPath, $publicPath)) {
                throw new \RuntimeException('Gagal menyalin file preview.');
            }

            $relativePath = $this->getPreviewRelativePath($token);
            $publicUrl = asset($relativePath);

            return response()->json([
                'file_url' => $publicUrl,
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            Log::error('Preview DOCX generation failed (TemplateProcessor)', [
                'template' => $template->id,
                'seminar' => $seminar->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal membuat preview dokumen.',
            ], 500);
        } finally {
            if ($docxPath && file_exists($docxPath)) {
                @unlink($docxPath);
            }
        }
    }

    public function cleanupPreviewDocx(Request $request)
    {
        $this->cleanupPreviewToken($request->input('token'));

        return response()->json(['status' => 'ok']);
    }

    public function sendEmail(Request $request, DocumentTemplate $template, Seminar $seminar)
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'email',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string',
            'attachment_mode' => 'required|in:auto,custom',
            'custom_attachment' => 'required_if:attachment_mode,custom|file|max:15360',
            'preview_token' => 'nullable|string',
        ]);

        $docxPath = null;
        $customAttachmentPath = null;
        $attachmentPath = null;
        $attachmentName = null;
        $baseData = null;
        $finalData = null;
        $emailPlaceholderData = null;

        try {
            Log::info('Starting email send', [
                'attachment_mode' => $request->input('attachment_mode', 'auto'),
                'template_id' => $template->id,
                'seminar_id' => $seminar->id,
            ]);

            $baseData = $this->getSeminarData($seminar);
            $finalData = $this->buildFinalData($template, $baseData);
            $emailPlaceholderData = $this->buildEmailPlaceholderDataset($template, $seminar, $baseData, $finalData);

            if ($request->input('attachment_mode', 'auto') === 'auto') {
                $docxPath = $this->generateDocxFromTemplate($template, $finalData);
                $attachmentPath = $docxPath;

                $attachmentName = $this->buildDownloadName(
                    $seminar->mahasiswa?->nama ?? 'Mahasiswa',
                    $template->nama ?? 'Template',
                    'docx'
                );
            }

            if ($request->input('attachment_mode') === 'custom' && $request->hasFile('custom_attachment')) {
                $storedPath = $request->file('custom_attachment')->store('temp/email-attachments');
                $customAttachmentPath = storage_path('app/'.$storedPath);
                $attachmentPath = $customAttachmentPath;

                $originalName = pathinfo($request->file('custom_attachment')->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $request->file('custom_attachment')->getClientOriginalExtension();
                $safeName = $this->sanitizeFileSegment($originalName ?: 'Lampiran');
                $attachmentName = $extension ? $safeName.'.'.$extension : $safeName;
            }

            if (! $attachmentPath || ! $attachmentName) {
                throw new \RuntimeException('Lampiran email tidak tersedia.');
            }

            $renderedSubject = $this->replaceEmailPlaceholders($request->subject, $emailPlaceholderData, true);
            $renderedBody = $this->replaceEmailPlaceholders($request->message ?? '', $emailPlaceholderData, false);

            foreach ($request->recipients as $recipient) {
                Log::info('Sending email to recipient', ['recipient' => $recipient]);
                Mail::send([], [], function ($message) use ($recipient, $attachmentPath, $attachmentName, $renderedSubject, $renderedBody) {
                    $message->to($recipient)
                        ->subject($renderedSubject)
                        ->setBody($renderedBody, 'text/html')
                        ->attach($attachmentPath, ['as' => $attachmentName]);
                });
            }

            if ($docxPath && file_exists($docxPath)) {
                @unlink($docxPath);
                Log::info('Temporary DOCX file deleted after email');
            }
            if ($customAttachmentPath && file_exists($customAttachmentPath)) {
                @unlink($customAttachmentPath);
            }

            return redirect()->back()->with('success', 'Email berhasil dikirim.');
        } catch (\Throwable $e) {
            Log::error('Send document email failed', [
                'template' => $template->id,
                'seminar' => $seminar->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($docxPath && file_exists($docxPath)) {
                @unlink($docxPath);
            }
            if ($customAttachmentPath && file_exists($customAttachmentPath)) {
                @unlink($customAttachmentPath);
            }

            return redirect()->back()->with('error', 'Gagal mengirim email: '.$e->getMessage());
        } finally {
            $this->cleanupPreviewToken($request->input('preview_token'));
        }
    }

    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function getTemplateFilePath(DocumentTemplate $template): string
    {
        return storage_path('app/private/'.ltrim($template->file_path, '/'));
    }

    private function normalizeAssociativeArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function buildFinalData(DocumentTemplate $template, array $baseData): array
    {
        $finalData = [];
        $availableTags = $template->available_tags;
        if (! is_array($availableTags)) {
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
        return $this->generateDocxFromTemplate($template, $data);
    }

    /**
     * Generate DOCX from template using TemplateProcessor with text/HTML/image support.
     */
    private function generateDocxFromTemplate(DocumentTemplate $template, array $finalData): string
    {
        $templatePath = $this->getTemplateFilePath($template);

        if (! file_exists($templatePath)) {
            throw new \Exception('Template file not found: '.$templatePath);
        }

        $tempDir = storage_path('app/temp/documents');
        $this->ensureDirectory($tempDir);

        $fileName = uniqid('doc_', true).'.docx';
        $outputPath = $tempDir.DIRECTORY_SEPARATOR.$fileName;

        $availableTags = $template->available_tags;
        if (! is_array($availableTags)) {
            $availableTags = json_decode($availableTags ?? '[]', true) ?: [];
        }

        $normalizedTemplatePath = $this->normalizeTemplatePlaceholdersForTemplateProcessor(
            $templatePath,
            $availableTags,
            $tempDir
        );

        $processor = new TemplateProcessor($normalizedTemplatePath);

        $tagTypes = $this->normalizeAssociativeArray($template->tag_types ?? []);
        $tagProps = $this->normalizeAssociativeArray($template->tag_properties ?? []);

        foreach ($availableTags as $tag) {
            $type = $tagTypes[$tag] ?? 'standard';
            $props = $tagProps[$tag] ?? [];
            $value = $finalData[$tag] ?? '';

            $variants = $this->resolveTagVariants($tag);

            switch ($type) {
                case 'image':
                    $this->applyImageValue($processor, $variants, is_string($value) ? $value : null, (array) $props);
                    break;

                case 'html':
                    if (is_string($value) && $value !== '') {
                        $this->applyHtmlValue($processor, $variants, $value);
                    } else {
                        foreach ($variants as $v) {
                            $processor->setValue($v, '');
                        }
                    }
                    break;

                default:
                    if (is_string($value) && $this->containsHtml($value)) {
                        $this->applyHtmlValue($processor, $variants, $value);
                    } else {
                        $text = is_scalar($value) ? $this->sanitizeXmlText((string) $value) : '';
                        foreach ($variants as $v) {
                            $processor->setValue($v, $text);
                        }
                    }
                    break;
            }
        }

        $processor->saveAs($outputPath);

        return $outputPath;
    }

    private function normalizeTemplatePlaceholdersForTemplateProcessor(string $templatePath, array $availableTags, string $tempDir): string
    {
        $needsRewrite = false;
        $replacementMap = [];

        foreach ($availableTags as $tag) {
            $tag = trim((string) $tag);
            if ($tag === '') {
                continue;
            }

            // Support legacy "$tag" placeholders by converting them to "${tag}" (TemplateProcessor format).
            $replacementMap['$'.$tag] = '${'.$tag.'}';
        }

        if (empty($replacementMap)) {
            return $templatePath;
        }

        $zip = new \ZipArchive;
        if ($zip->open($templatePath) !== true) {
            return $templatePath;
        }

        $segments = [
            'word/document.xml',
            'word/header1.xml',
            'word/header2.xml',
            'word/header3.xml',
            'word/footer1.xml',
            'word/footer2.xml',
            'word/footer3.xml',
        ];

        foreach ($segments as $segment) {
            $content = $zip->getFromName($segment);
            if (! is_string($content) || $content === '') {
                continue;
            }

            $newContent = strtr($content, $replacementMap);
            if ($newContent !== $content) {
                $needsRewrite = true;
                break;
            }
        }

        $zip->close();

        if (! $needsRewrite) {
            return $templatePath;
        }

        $normalizedPath = $tempDir.DIRECTORY_SEPARATOR.'normalized_template_'.uniqid('', true).'.docx';
        if (! @copy($templatePath, $normalizedPath)) {
            return $templatePath;
        }

        $zip = new \ZipArchive;
        if ($zip->open($normalizedPath) !== true) {
            return $templatePath;
        }

        foreach ($segments as $segment) {
            $content = $zip->getFromName($segment);
            if (! is_string($content) || $content === '') {
                continue;
            }

            $newContent = strtr($content, $replacementMap);
            if ($newContent !== $content) {
                $zip->deleteName($segment);
                $zip->addFromString($segment, $newContent);
            }
        }

        $zip->close();

        return $normalizedPath;
    }

    // ... (helper methods getSeminarData, buildEmailDefaults, applyHtmlValue, applyImageValue, etc. remain below)
    // Helper methods continued...

    private function resolveTagVariants(string $tag): array
    {
        $clean = trim($tag);
        if ($clean === '') {
            return [];
        }

        $normalized = preg_replace('/\s+/', '_', $clean);
        $variants = [
            $clean,
            $normalized,
            strtolower($normalized),
            strtoupper($normalized),
        ];

        return array_values(array_unique(array_filter($variants)));
    }

    private function applyImageValue(TemplateProcessor $processor, array $variants, ?string $value, array $properties = []): void
    {
        $path = $value ?? '';
        if ($path && ! file_exists($path) && str_starts_with($path, 'data:image')) {
            $path = $this->convertBase64ToImage($path, 'image_tag');
        }

        if (! $path || ! file_exists($path)) {
            foreach ($variants as $variant) {
                $processor->setValue($variant, '');
            }

            return;
        }

        [$width, $height] = $this->resolveImageDimensions($properties);

        // Validate image before processing
        $imageInfo = @getimagesize($path);
        if ($imageInfo === false) {
            Log::warning('Invalid image file', ['path' => $path]);
            foreach ($variants as $variant) {
                $processor->setValue($variant, '');
            }

            return;
        }

        // Set reasonable default dimensions if not specified
        $width = $width ?: min($imageInfo[0], 300);
        $height = $height ?: min($imageInfo[1], 150);

        $options = [
            'path' => $path,
            'width' => $width,
            'height' => $height,
        ];

        try {
            $processor->setImageValue($variants[0], $options);

            if (count($variants) > 1) {
                foreach (array_slice($variants, 1) as $variant) {
                    $processor->setValue($variant, '');
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to set image value', [
                'path' => $path,
                'error' => $e->getMessage(),
                'tag' => $variants[0] ?? 'unknown',
            ]);

            // Fallback to empty value if image insertion fails
            foreach ($variants as $variant) {
                $processor->setValue($variant, '');
            }
        }
    }

    private function containsHtml(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $trimmed = trim($decoded);
        if ($trimmed === '') {
            return false;
        }

        if ($trimmed !== strip_tags($trimmed)) {
            return true;
        }

        return (bool) preg_match('/<\/?[a-z][^>]*>/i', $trimmed);
    }

    private function applyHtmlValue(TemplateProcessor $processor, array $variants, string $html): void
    {
        $decoded = $this->sanitizeXmlText(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plain = trim(strip_tags($decoded));
        foreach ($variants as $variant) {
            $this->replaceHtmlOccurrences($processor, $variant, $decoded, $plain);
        }
    }

    private function sanitizeXmlText(string $value): string
    {
        if ($value === '') {
            return '';
        }

        // Remove characters not allowed in XML 1.0, which can trigger Word "unreadable content" warnings.
        $value = preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $value) ?? '';

        return $value;
    }

    private function buildHtmlTextRun(string $html, array $baseStyle = []): ?TextRun
    {
        $textRun = new TextRun;
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousSetting = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="utf-8"?><div>'.$html.'</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );
        } catch (\Throwable $exception) {
            Log::warning('Gagal mem-parsing HTML', ['error' => $exception->getMessage()]);
            libxml_clear_errors();
            libxml_use_internal_errors($previousSetting);

            return null;
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        $root = $document->documentElement;
        if (! $root) {
            return null;
        }

        $this->appendHtmlChildren($root, $textRun, [], $baseStyle);

        return $textRun;
    }

    private function appendHtmlChildren(DOMNode $node, TextRun $textRun, array $styleStack, array $baseStyle): void
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $compressed = preg_replace('/\s+/u', ' ', $child->nodeValue ?? '');
                $text = html_entity_decode($compressed, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($text !== '') {
                    $textRun->addText($text, $this->buildTextStyle($baseStyle, $styleStack));
                }

                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($child->nodeName);
            switch ($tag) {
                case 'strong':
                case 'b':
                    $styleStack['bold'] = true;
                    $this->appendHtmlChildren($child, $textRun, $styleStack, $baseStyle);
                    unset($styleStack['bold']);
                    break;
                case 'em':
                case 'i':
                    $styleStack['italic'] = true;
                    $this->appendHtmlChildren($child, $textRun, $styleStack, $baseStyle);
                    unset($styleStack['italic']);
                    break;
                case 'u':
                    $styleStack['underline'] = 'single';
                    $this->appendHtmlChildren($child, $textRun, $styleStack, $baseStyle);
                    unset($styleStack['underline']);
                    break;
                case 's':
                case 'strike':
                    $styleStack['strikethrough'] = true;
                    $this->appendHtmlChildren($child, $textRun, $styleStack, $baseStyle);
                    unset($styleStack['strikethrough']);
                    break;
                case 'span':
                    $childStyle = $this->applyInlineStyles($styleStack, $child);
                    $this->appendHtmlChildren($child, $textRun, $childStyle, $baseStyle);
                    break;
                case 'br':
                    $textRun->addTextBreak();
                    break;
                case 'p':
                case 'div':
                    $this->appendHtmlChildren($child, $textRun, $styleStack, $baseStyle);
                    break;
                case 'ul':
                case 'ol':
                    $this->appendHtmlList($child, $textRun, $styleStack, $baseStyle, $tag === 'ol');
                    break;
                default:
                    $this->appendHtmlChildren($child, $textRun, $styleStack, $baseStyle);
                    break;
            }
        }
    }

    private function appendHtmlList(DOMNode $node, TextRun $textRun, array $styleStack, array $baseStyle, bool $ordered): void
    {
        $index = 0;
        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE || strtolower($child->nodeName) !== 'li') {
                continue;
            }

            $index++;
            $prefix = $ordered ? $index.'. ' : "\u{2022} ";
            $textRun->addText($prefix, $this->buildTextStyle($baseStyle, $styleStack));
            $this->appendHtmlChildren($child, $textRun, $styleStack, $baseStyle);
            $textRun->addTextBreak();
        }

        if ($index > 0) {
            $textRun->addTextBreak();
        }
    }

    private function applyInlineStyles(array $styleStack, DOMNode $node): array
    {
        if (! $node instanceof DOMElement) {
            return $styleStack;
        }

        $styleAttribute = $node->getAttribute('style');
        if (! $styleAttribute) {
            return $styleStack;
        }

        $declarations = explode(';', $styleAttribute);
        foreach ($declarations as $declaration) {
            if (trim($declaration) === '') {
                continue;
            }

            [$property, $value] = array_pad(array_map('trim', explode(':', $declaration, 2)), 2, '');
            $property = strtolower($property);
            $value = strtolower($value);

            switch ($property) {
                case 'font-weight':
                    if ($value === 'bold') {
                        $styleStack['bold'] = true;
                    }
                    break;
                case 'font-style':
                    if ($value === 'italic') {
                        $styleStack['italic'] = true;
                    }
                    break;
                case 'text-decoration':
                    if (str_contains($value, 'underline')) {
                        $styleStack['underline'] = 'single';
                    }
                    if (str_contains($value, 'line-through')) {
                        $styleStack['strikethrough'] = true;
                    }
                    break;
                case 'color':
                    $styleStack['color'] = ltrim($value, '#');
                    break;
            }
        }

        return $styleStack;
    }

    private function buildTextStyle(array $baseStyle, array $overrides): array
    {
        $style = $baseStyle;

        if (! empty($overrides['bold'])) {
            $style['bold'] = true;
        }
        if (! empty($overrides['italic'])) {
            $style['italic'] = true;
        }
        if (! empty($overrides['underline'])) {
            $style['underline'] = $overrides['underline'];
        }
        if (! empty($overrides['strikethrough'])) {
            $style['strikethrough'] = true;
        }
        if (! empty($overrides['color'])) {
            $style['color'] = ltrim($overrides['color'], '#');
        }

        return $style;
    }

    private function replaceHtmlOccurrences(TemplateProcessor $processor, string $tag, string $html, string $plain): void
    {
        $maxAttempts = 50;
        $attempt = 0;

        while ($attempt < $maxAttempts && $this->macroExistsInMainPart($processor, $tag)) {
            $style = $this->extractRunStyle($processor, $tag);
            $textRun = $this->buildHtmlTextRun($html, $style);
            if (! $textRun) {
                break;
            }

            $processor->setComplexValue($tag, $textRun);
            $attempt++;
        }

        if ($this->macroExistsAnywhere($processor, $tag)) {
            $processor->setValue($tag, $plain);
        }
    }

    private function macroExistsInMainPart(TemplateProcessor $processor, string $tag): bool
    {
        $macro = $this->completeMacroSyntax($tag);
        $mainPart = $this->getTemplateProcessorProperty($processor, 'tempDocumentMainPart');

        return is_string($mainPart) && strpos($mainPart, $macro) !== false;
    }

    private function macroExistsAnywhere(TemplateProcessor $processor, string $tag): bool
    {
        $macro = $this->completeMacroSyntax($tag);
        foreach ($this->getTemplateXmlParts($processor) as $part) {
            if (strpos($part, $macro) !== false) {
                return true;
            }
        }

        return false;
    }

    private function completeMacroSyntax(string $tag): string
    {
        $prefix = '${';

        return str_starts_with($tag, $prefix) ? $tag : $prefix.$tag.'}';
    }

    private function getTemplateXmlParts(TemplateProcessor $processor): array
    {
        $parts = [];
        $main = $this->getTemplateProcessorProperty($processor, 'tempDocumentMainPart');
        if (is_string($main)) {
            $parts[] = $main;
        }

        $headers = $this->getTemplateProcessorProperty($processor, 'tempDocumentHeaders');
        if (is_array($headers)) {
            foreach ($headers as $header) {
                if (is_string($header)) {
                    $parts[] = $header;
                }
            }
        }

        $footers = $this->getTemplateProcessorProperty($processor, 'tempDocumentFooters');
        if (is_array($footers)) {
            foreach ($footers as $footer) {
                if (is_string($footer)) {
                    $parts[] = $footer;
                }
            }
        }

        return $parts;
    }

    private function getTemplateProcessorProperty(TemplateProcessor $processor, string $property)
    {
        if (! isset($this->templatePropertyCache[$property])) {
            $reflection = new ReflectionProperty(TemplateProcessor::class, $property);
            $reflection->setAccessible(true);
            $this->templatePropertyCache[$property] = $reflection;
        }

        return $this->templatePropertyCache[$property]->getValue($processor);
    }

    private function extractRunStyle(TemplateProcessor $processor, string $tag): array
    {
        $macro = $this->completeMacroSyntax($tag);
        foreach ($this->getTemplateXmlParts($processor) as $part) {
            $position = strpos($part, $macro);
            if ($position === false) {
                continue;
            }

            $runXml = $this->captureRunXml($part, $position);
            if (! $runXml) {
                continue;
            }

            $style = $this->convertRunPropertiesToStyle($runXml);
            if (! empty($style)) {
                return $style;
            }
        }

        return $this->defaultRunStyle();
    }

    private function captureRunXml(string $xml, int $position): ?string
    {
        $before = substr($xml, 0, $position);
        $start = strrpos($before, '<w:r');
        if ($start === false) {
            return null;
        }

        $end = strpos($xml, '</w:r>', $start);
        if ($end === false) {
            return null;
        }

        $end += 6;

        return substr($xml, $start, $end - $start);
    }

    private function convertRunPropertiesToStyle(string $runXml): array
    {
        if (! preg_match('/<w:rPr[^>]*>.*?<\/w:rPr>/s', $runXml, $matches)) {
            return [];
        }

        $xml = '<root xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'.$matches[0].'</root>';
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousSetting = libxml_use_internal_errors(true);

        if (! $document->loadXML($xml)) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousSetting);

            return [];
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $style = [];

        $fontNode = $xpath->query('//w:rFonts')->item(0);
        if ($fontNode instanceof DOMElement) {
            $font = $fontNode->getAttribute('w:ascii') ?: $fontNode->getAttribute('w:hAnsi') ?: $fontNode->getAttribute('w:cs');
            if ($font) {
                $style['name'] = $font;
            }
        }

        $sizeNode = $xpath->query('//w:sz')->item(0);
        if ($sizeNode instanceof DOMElement) {
            $size = (int) $sizeNode->getAttribute('w:val');
            if ($size > 0) {
                $style['size'] = $size / 2;
            }
        }

        $colorNode = $xpath->query('//w:color')->item(0);
        if ($colorNode instanceof DOMElement) {
            $color = $colorNode->getAttribute('w:val');
            if ($color) {
                $style['color'] = ltrim($color, '#');
            }
        }

        if ($xpath->query('//w:b')->length > 0) {
            $style['bold'] = true;
        }
        if ($xpath->query('//w:i')->length > 0) {
            $style['italic'] = true;
        }

        $underlineNode = $xpath->query('//w:u')->item(0);
        if ($underlineNode instanceof DOMElement) {
            $underline = $underlineNode->getAttribute('w:val') ?: 'single';
            if (strtolower($underline) !== 'none') {
                $style['underline'] = $underline;
            }
        }

        if ($xpath->query('//w:strike')->length > 0 || $xpath->query('//w:dstrike')->length > 0) {
            $style['strikethrough'] = true;
        }

        return $style;
    }

    private function defaultRunStyle(): array
    {
        return [
            'name' => 'Times New Roman',
            'size' => 12,
        ];
    }

    private function buildDownloadName(string $studentName, string $templateName, string $extension): string
    {
        $student = $this->sanitizeFileSegment($studentName) ?: 'Mahasiswa';
        $template = $this->sanitizeFileSegment($templateName) ?: 'Template';
        $extension = ltrim($extension, '.');

        return trim($student.' '.$template).'.'.$extension;
    }

    private function sanitizeFileSegment(string $value): string
    {
        $value = preg_replace('/[^\pL\pN ._-]+/u', '', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private function buildEmailDefaults(DocumentTemplate $template, Seminar $seminar, array $baseData, array $finalData): array
    {
        $emailData = $this->buildEmailPlaceholderDataset($template, $seminar, $baseData, $finalData);

        $subjectTemplate = $template->email_subject_template ?: $this->resolveEmailSubjectTemplate(null);
        $bodyTemplate = $template->email_body_template ?: $this->resolveEmailBodyTemplate(null);

        return [
            'subject' => $this->replaceEmailPlaceholders($subjectTemplate, $emailData, true),
            'body' => $this->replaceEmailPlaceholders($bodyTemplate, $emailData, false),
        ];
    }

    private function buildEmailPlaceholderDataset(DocumentTemplate $template, Seminar $seminar, array $baseData, array $finalData): array
    {
        $dataset = array_merge($baseData, $finalData);
        $dataset['template_nama'] = $template->nama ?? '';
        $dataset['template_kode'] = $template->kode ?? '';
        $dataset['seminar_id'] = (string) $seminar->id;

        return $this->applyEmailDatasetAliases($dataset);
    }

    private function applyEmailDatasetAliases(array $dataset): array
    {
        $aliases = [];
        $prefixes = ['mahasiswa_', 'seminar_', 'p1_', 'p2_', 'pembahas_', 'nilai_'];

        foreach ($dataset as $key => $value) {
            foreach ($prefixes as $prefix) {
                if (! str_starts_with($key, $prefix)) {
                    continue;
                }

                $alias = substr($key, strlen($prefix));
                if ($alias === '' || array_key_exists($alias, $dataset) || array_key_exists($alias, $aliases)) {
                    continue;
                }

                $aliases[$alias] = $value;
            }
        }

        $manualAliases = [
            'nama' => 'mahasiswa_nama',
            'npm' => 'mahasiswa_npm',
            'prodi' => 'mahasiswa_prodi',
            'email' => 'mahasiswa_email',
            'no_hp' => 'mahasiswa_no_hp',
            'judul' => 'seminar_judul',
            'tanggal' => 'seminar_tanggal',
            'tahun' => 'seminar_tahun',
            'hari' => 'seminar_hari',
            'lokasi' => 'seminar_lokasi',
            'ttdp1' => 'p1_ttd',
            'ttdp2' => 'p2_ttd',
            'ttdpembahas' => 'pembahas_ttd',
        ];

        foreach ($manualAliases as $alias => $sourceKey) {
            if (array_key_exists($alias, $dataset) || array_key_exists($alias, $aliases)) {
                continue;
            }

            if (array_key_exists($sourceKey, $dataset)) {
                $aliases[$alias] = $dataset[$sourceKey];
            }
        }

        return $dataset + $aliases;
    }

    private function replaceEmailPlaceholders(string $text, array $data, bool $stripHtml = false): string
    {
        if ($text === '') {
            return $text;
        }

        $normalized = [];
        foreach ($data as $key => $value) {
            $normalized[strtolower($key)] = $this->normalizeEmailDataValue($value, $stripHtml);
        }

        return (string) preg_replace_callback(
            '/(\{\{|\[\[|\$\{)(.+?)(\}\}|\]\]|\})/is',
            function ($matches) use ($normalized) {
                $extractedKey = $this->extractPlaceholderKey($matches[2]);
                if ($extractedKey === null) {
                    return '';
                }

                return $normalized[$extractedKey] ?? '';
            },
            $text
        );
    }

    private function normalizeEmailDataValue($value, bool $stripHtml = false): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        $text = (string) $value;
        if ($text === '') {
            return '';
        }

        if (! $stripHtml) {
            $mediaMarkup = $this->resolveEmailMediaMarkup($text);
            if ($mediaMarkup !== null) {
                return $mediaMarkup;
            }
        }

        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (! $stripHtml) {
            return $decoded;
        }

        $decoded = preg_replace('/<br\s*\/?/i', "\n", $decoded);
        $decoded = preg_replace('/<\/(p|div|li|tr|table|ul|ol|h[1-6])>/i', "\n", $decoded);
        $decoded = preg_replace('/<(p|div|li|tr|table|ul|ol|h[1-6])[^>]*>/i', "\n", $decoded);
        $stripped = strip_tags($decoded);
        $stripped = str_replace(["\r\n", "\r"], "\n", $stripped);
        $stripped = preg_replace("/[\t ]+/u", ' ', $stripped);
        $stripped = preg_replace("/\n{3,}/u", "\n\n", $stripped);

        return trim($stripped);
    }

    private function extractPlaceholderKey(string $raw): ?string
    {
        $stripped = strip_tags($raw);
        $clean = strtolower(preg_replace('/[^a-z0-9_]+/i', '', $stripped));

        return $clean !== '' ? $clean : null;
    }

    private function resolveEmailMediaMarkup(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if ($this->looksLikeDataUriImage($trimmed)) {
            return $this->wrapImageForEmail($trimmed);
        }

        $pathCandidates = [
            $trimmed,
            storage_path('app/'.ltrim($trimmed, '/')),
            storage_path('app/public/'.ltrim($trimmed, '/')),
            public_path($trimmed),
            public_path('storage/'.ltrim($trimmed, '/')),
        ];

        foreach ($pathCandidates as $candidate) {
            if (! $candidate || ! is_file($candidate)) {
                continue;
            }

            $dataUri = $this->convertImageFileToDataUri($candidate);
            if ($dataUri) {
                return $this->wrapImageForEmail($dataUri);
            }
        }

        return null;
    }

    private function looksLikeDataUriImage(string $value): bool
    {
        return str_starts_with($value, 'data:image/');
    }

    private function convertImageFileToDataUri(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $contents = file_get_contents($path);
        if ($contents === false || $contents === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    private function wrapImageForEmail(string $dataUri): string
    {
        return '<img src="'.e($dataUri).'" alt="" style="max-width:320px;height:auto;">';
    }

    private function resolveEmailSubjectTemplate(?string $value): string
    {
        $subject = trim((string) $value);

        return $subject === ''
            ? 'Dokumen {{template_nama}} - {{mahasiswa_nama}}'
            : $subject;
    }

    private function resolveEmailBodyTemplate(?string $value): string
    {
        $body = trim((string) $value);

        return $body === ''
            ? "Yth. {{mahasiswa_nama}},\n\nBerikut kami kirimkan dokumen {{template_nama}}.\n\nTerima kasih."
            : $body;
    }

    private function getPreviewStorageDirectory(): string
    {
        $path = public_path(self::PREVIEW_RELATIVE_PATH);
        $this->ensureDirectory($path);

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    private function getPreviewRelativePath(string $token): string
    {
        return trim(self::PREVIEW_RELATIVE_PATH.'/'.ltrim($token, '/'), '/');
    }

    private function cleanupPreviewToken(?string $token): void
    {
        $sanitized = $this->sanitizePreviewToken($token);
        if (! $sanitized) {
            return;
        }

        $path = $this->getPreviewStorageDirectory().DIRECTORY_SEPARATOR.$sanitized;
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    private function sanitizePreviewToken(?string $token): ?string
    {
        if (! $token) {
            return null;
        }

        $clean = basename($token);
        if (! preg_match('/^[A-Za-z0-9_.-]+\.docx$/', $clean)) {
            return null;
        }

        return $clean;
    }

    private function getSeminarData(Seminar $seminar): array
    {
        $seminar->loadMissing([
            'mahasiswa',
            'seminarJenis',
            'p1Dosen',
            'p2Dosen',
            'pembahasDosen',
            'nilai',
            'signatures',
        ]);

        $nilaiData = $this->calculateNilai($seminar);

        $p1Signature = $seminar->signatures->firstWhere('dosen_id', $seminar->p1_dosen_id);
        $p2Signature = $seminar->signatures->firstWhere('dosen_id', $seminar->p2_dosen_id);
        $pembahasSignature = $seminar->signatures->firstWhere('dosen_id', $seminar->pembahas_dosen_id);

        $p1SignaturePath = $this->convertBase64ToImage($p1Signature->tanda_tangan ?? null, 'p1_'.$seminar->id);
        $p2SignaturePath = $this->convertBase64ToImage($p2Signature->tanda_tangan ?? null, 'p2_'.$seminar->id);
        $pembahasSignaturePath = $this->convertBase64ToImage($pembahasSignature->tanda_tangan ?? null, 'pembahas_'.$seminar->id);

        $hariNama = '';
        $tahun = '';
        if ($seminar->tanggal) {
            $hariIndo = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
            ];
            $hariNama = $hariIndo[$seminar->tanggal->format('l')] ?? '';
            $tahun = $seminar->tanggal->format('Y');
        }

        return array_merge($nilaiData, [
            'mahasiswa_nama' => $seminar->mahasiswa?->nama ?? '',
            'mahasiswa_npm' => $seminar->mahasiswa?->npm ?? '',
            'mahasiswa_prodi' => $seminar->mahasiswa?->prodi ?? '',
            'mahasiswa_email' => $seminar->mahasiswa?->email ?? '',
            'mahasiswa_no_hp' => $seminar->mahasiswa?->no_hp ?? '',
            'seminar_no_surat' => $seminar->no_surat ?? '',
            'seminar_judul' => $seminar->judul ?? '',
            'seminar_tanggal' => $seminar->tanggal ? $seminar->tanggal->format('d F Y') : '',
            'seminar_tahun' => $tahun,
            'seminar_hari' => $hariNama,
            'seminar_waktu_mulai' => $seminar->waktu_mulai ?? '',
            'seminar_lokasi' => $seminar->lokasi ?? '',
            'seminar_status' => $seminar->status ?? '',
            'seminar_jenis_nama' => $seminar->seminarJenis?->nama ?? '',
            'p1_nama' => $seminar->p1Dosen?->nama ?? '',
            'p1_nip' => $seminar->p1Dosen?->nip ?? '',
            'p1_email' => $seminar->p1Dosen?->email ?? '',
            'p1_ttd' => $p1SignaturePath,
            'p2_nama' => $seminar->p2Dosen?->nama ?? '',
            'p2_nip' => $seminar->p2Dosen?->nip ?? '',
            'p2_email' => $seminar->p2Dosen?->email ?? '',
            'p2_ttd' => $p2SignaturePath,
            'pembahas_nama' => $seminar->pembahasDosen?->nama ?? '',
            'pembahas_nip' => $seminar->pembahasDosen?->nip ?? '',
            'pembahas_email' => $seminar->pembahasDosen?->email ?? '',
            'pembahas_ttd' => $pembahasSignaturePath,
        ]);
    }

    private function calculateNilai(Seminar $seminar): array
    {
        $nilaiP1Model = $seminar->nilai()->where('dosen_id', $seminar->p1_dosen_id)->first();
        $nilaiP2Model = $seminar->nilai()->where('dosen_id', $seminar->p2_dosen_id)->first();
        $nilaiPembahasModel = $seminar->nilai()->where('dosen_id', $seminar->pembahas_dosen_id)->first();

        $p1Score = $this->getEvaluatorScore($nilaiP1Model);
        $p2Score = $this->getEvaluatorScore($nilaiP2Model);
        $pembahasScore = $this->getEvaluatorScore($nilaiPembahasModel);

        $weights = $this->resolveNilaiWeights($seminar);
        $p1Weighted = $this->applyWeight($p1Score, $weights['p1']);
        $p2Weighted = $this->applyWeight($p2Score, $weights['p2']);
        $pembahasWeighted = $this->applyWeight($pembahasScore, $weights['pembahas']);

        $nilaiAkhir = $p1Weighted + $p2Weighted + $pembahasWeighted;
        $nilaiHuruf = $this->getNilaiHuruf($nilaiAkhir, $seminar);
        $nilaiTerbilang = $this->formatTerbilang($nilaiAkhir);

        return [
            'nilai_p1' => $this->formatNumeric($p1Score),
            'nilai_p2' => $this->formatNumeric($p2Score),
            'nilai_pembahas' => $this->formatNumeric($pembahasScore),
            'bbtp1' => $this->formatNumeric($weights['p1']),
            'bbtp2' => $this->formatNumeric($weights['p2']),
            'bbtp3' => $this->formatNumeric($weights['pembahas']),
            'nialip1xbbt1' => $this->formatNumeric($p1Weighted),
            'nilaip2xbbt2' => $this->formatNumeric($p2Weighted),
            'nilaipmbxbbtpmb' => $this->formatNumeric($pembahasWeighted),
            'nilai_bobot' => $this->formatNumeric($nilaiAkhir),
            'nilai_bobot_total' => $this->formatNumeric($nilaiAkhir),
            'nilai_akhir' => $this->formatNumeric($nilaiAkhir),
            'nilai_huruf' => $nilaiHuruf,
            'nilai_akhir_terbilang' => $nilaiTerbilang,
            'nilai_terbilang' => $nilaiTerbilang,
            'dinyatakan' => $this->resolveKelulusanStatus($nilaiHuruf),
            'diperkenankan' => $this->resolvePerkenanStatus($nilaiHuruf),
        ];
    }

    private function getEvaluatorScore(?SeminarNilai $nilai): float
    {
        if (! $nilai || $nilai->nilai_angka === null) {
            return 0.0;
        }

        return (float) $nilai->nilai_angka;
    }

    private function resolveNilaiWeights(Seminar $seminar): array
    {
        $jenis = $seminar->seminarJenis;

        $weights = [
            'p1' => (float) ($seminar->seminarJenis?->p1_weight ?? 40),
            'p2' => (float) ($seminar->seminarJenis?->p2_weight ?? 40),
            'pembahas' => (float) ($seminar->seminarJenis?->pembahas_weight ?? 20),
        ];

        $requiredFlags = [
            'p1' => (bool) ($jenis?->p1_required ?? true),
            'p2' => (bool) ($jenis?->p2_required ?? true),
            'pembahas' => (bool) ($jenis?->pembahas_required ?? true),
        ];

        foreach ($requiredFlags as $key => $required) {
            if (! $required) {
                $weights[$key] = 0.0;
            }
        }

        $total = $weights['p1'] + $weights['p2'] + $weights['pembahas'];
        if ($total <= 0) {
            $fallback = [
                'p1' => $requiredFlags['p1'] ? 40.0 : 0.0,
                'p2' => $requiredFlags['p2'] ? 40.0 : 0.0,
                'pembahas' => $requiredFlags['pembahas'] ? 20.0 : 0.0,
            ];

            $fallbackTotal = $fallback['p1'] + $fallback['p2'] + $fallback['pembahas'];
            if ($fallbackTotal <= 0) {
                return [
                    'p1' => 40.0,
                    'p2' => 40.0,
                    'pembahas' => 20.0,
                ];
            }

            foreach ($fallback as $key => $value) {
                $fallback[$key] = ($value / $fallbackTotal) * 100;
            }

            return $fallback;
        }

        if (abs($total - 100) <= 0.01) {
            return $weights;
        }

        foreach ($weights as $key => $value) {
            $weights[$key] = ($value / $total) * 100;
        }

        return $weights;
    }

    private function applyWeight(float $score, float $weight): float
    {
        return $score * $weight / 100;
    }

    private function formatNumeric(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, '.', '');
    }

    private function formatTerbilang(float $value): string
    {
        $converted = Terbilang::convert(number_format($value, 2, '.', ''));

        return is_string($converted) ? ucwords($converted) : '';
    }

    private function resolveKelulusanStatus(?string $nilaiHuruf): string
    {
        $grade = strtoupper(trim((string) $nilaiHuruf));
        if ($grade === '') {
            return '';
        }

        $nonPassing = ['D', 'E', 'F'];

        return in_array($grade, $nonPassing, true) ? 'Tidak Lulus' : 'Lulus';
    }

    private function resolvePerkenanStatus(?string $nilaiHuruf): string
    {
        $grade = strtoupper(trim((string) $nilaiHuruf));
        if ($grade === '') {
            return '';
        }

        $nonPassing = ['D', 'E', 'F'];

        return in_array($grade, $nonPassing, true) ? 'Tidak Diperkenankan' : 'Diperkenankan';
    }

    private function resolveImageDimensions(array $properties): array
    {
        $width = $properties['image_width'] ?? $properties['width'] ?? null;
        $height = $properties['image_height'] ?? $properties['height'] ?? null;

        return [
            $this->sanitizeImageDimension($width, 120),
            $this->sanitizeImageDimension($height, 60),
        ];
    }

    private function sanitizeImageDimension($value, int $fallback): int
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        $numeric = (int) $value;

        return $numeric > 0 ? $numeric : $fallback;
    }

    private function getNilaiHuruf(float $nilai, Seminar $seminar): string
    {
        $rounded = round($nilai, 1);
        $scheme = $seminar->seminarJenis?->grading_scheme ?? null;

        if (is_array($scheme)) {
            foreach ($scheme as $entry) {
                $min = $entry['min'] ?? null;
                $max = $entry['max'] ?? null;
                if ($min === null || $max === null) {
                    continue;
                }

                if ($rounded >= $min && $rounded <= $max) {
                    return (string) ($entry['grade'] ?? '');
                }
            }
        }

        if ($rounded >= 76) {
            return 'A';
        }
        if ($rounded >= 71) {
            return 'B+';
        }
        if ($rounded >= 66) {
            return 'B';
        }
        if ($rounded >= 61) {
            return 'C+';
        }
        if ($rounded >= 56) {
            return 'C';
        }
        if ($rounded >= 50) {
            return 'D';
        }

        return 'E';
    }

    private function convertBase64ToImage(?string $value, string $prefix): ?string
    {
        if (! $value) {
            return null;
        }

        $possiblePaths = [
            $value,
            storage_path('app/'.ltrim($value, '/')),
            storage_path('app/public/'.ltrim($value, '/')),
            public_path('storage/'.ltrim($value, '/')),
        ];

        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        if (! str_starts_with($value, 'data:image')) {
            return null;
        }

        $data = preg_replace('/^data:image\/\w+;base64,/', '', $value);
        $binary = base64_decode($data, true);
        if ($binary === false || strlen($binary) < 100) {
            return null;
        }

        $dir = storage_path('app/temp/signatures');
        $this->ensureDirectory($dir);
        $filePath = $dir.'/'.$prefix.'_'.uniqid('', true).'.png';

        if (file_put_contents($filePath, $binary) === false) {
            return null;
        }

        return $filePath;
    }

}
