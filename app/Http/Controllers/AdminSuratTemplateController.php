<?php

namespace App\Http\Controllers;

use App\Models\SuratJenis;
use App\Models\SuratTemplate;
use App\Models\Surat;
use App\Services\SuratTemplateService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminSuratTemplateController extends Controller
{
    public function index(SuratJenis $suratJenis)
    {
        $templates = SuratTemplate::where('surat_jenis_id', $suratJenis->id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.surattemplate.index', compact('suratJenis', 'templates'));
    }

    public function create(SuratJenis $suratJenis)
    {
        $availableFields = SuratTemplate::getAvailableFields($suratJenis);
        return view('admin.surattemplate.create', compact('suratJenis', 'availableFields'));
    }

    public function store(Request $request, SuratJenis $suratJenis)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'file' => 'required|file|mimes:docx|max:10240',
            'keterangan' => 'nullable|string',
        ]);

        $templateDir = storage_path('app/private/surat-templates');
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        $filename = uniqid('surat_template_', true) . '.docx';
        $request->file('file')->move($templateDir, $filename);
        $fullPath = $templateDir . '/' . $filename;

        $availableTags = SuratTemplate::extractTagsFromDocx($fullPath);

        $template = SuratTemplate::create([
            'surat_jenis_id' => $suratJenis->id,
            'nama' => $request->input('nama'),
            'file_path' => 'surat-templates/' . $filename,
            'keterangan' => $request->input('keterangan'),
            'available_tags' => $availableTags,
            'tag_mappings' => null,
            'tag_types' => null,
            'tag_properties' => null,
            'email_subject_template' => null,
            'email_body_template' => null,
            'aktif' => true,
        ]);

        $suratJenis->update(['template_id' => $template->id]);

        return redirect()->route('admin.surattemplate.edit', [$suratJenis, $template])
            ->with('success', 'Template berhasil diunggah. Silakan lengkapi mapping tag.');
    }

    public function edit(SuratJenis $suratJenis, SuratTemplate $template)
    {
        abort_unless((int) $template->surat_jenis_id === (int) $suratJenis->id, 404);
        $availableFields = SuratTemplate::getAvailableFields($suratJenis);
        $surats = Surat::with(['pemohonDosen', 'mahasiswa'])
            ->where('surat_jenis_id', $suratJenis->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('admin.surattemplate.edit', compact('suratJenis', 'template', 'availableFields', 'surats'));
    }

    public function update(Request $request, SuratJenis $suratJenis, SuratTemplate $template)
    {
        abort_unless((int) $template->surat_jenis_id === (int) $suratJenis->id, 404);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'tag_mappings' => 'nullable|array',
            'tag_types' => 'nullable|array',
            'email_subject_template' => 'nullable|string|max:255',
            'email_body_template' => 'nullable|string',
            'aktif' => 'nullable|boolean',
            'new_file' => 'nullable|file|mimes:docx|max:10240',
        ]);

        if ($request->hasFile('new_file')) {
            $templateDir = storage_path('app/private/surat-templates');
            if (!is_dir($templateDir)) {
                mkdir($templateDir, 0755, true);
            }

            $filename = uniqid('surat_template_', true) . '.docx';
            $request->file('new_file')->move($templateDir, $filename);
            $fullPath = $templateDir . '/' . $filename;
            $availableTags = SuratTemplate::extractTagsFromDocx($fullPath);

            $validated['file_path'] = 'surat-templates/' . $filename;
            $validated['available_tags'] = $availableTags;
        }

        $validated['aktif'] = (bool) ($request->input('aktif', true));
        $template->update($validated);

        return redirect()->route('admin.surattemplate.edit', [$suratJenis, $template])
            ->with('success', 'Template surat berhasil diperbarui.');
    }

    public function downloadDocx(SuratJenis $suratJenis, SuratTemplate $template, Surat $surat, SuratTemplateService $service)
    {
        abort_unless((int) $template->surat_jenis_id === (int) $suratJenis->id, 404);
        abort_unless((int) $surat->surat_jenis_id === (int) $suratJenis->id, 404);

        $outDir = base_path('uploads/documents/surat/generated');
        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }
        
        
        $pemohonName = $surat->pemohonDosen ? $surat->pemohonDosen->nama : ($surat->pemohonMahasiswa ? $surat->pemohonMahasiswa->nama : 'Unknown');
        $rawName = $pemohonName . ' - ' . $suratJenis->nama;
        // Remove only characters invalid in filenames: \ / : * ? " < > |
        $safeName = preg_replace('/[<>:"\/\\|?*]/', '', $rawName);
        $downloadName = trim($safeName) . '.docx';
        
        // Use a safe unique name for storage to prevent collisions
        $tempFilename = Str::uuid() . '.docx';
        $outPath = $outDir . '/' . $tempFilename;

        try {
            $service->generateDocx($template, $surat, $outPath);
            
            if (!file_exists($outPath)) {
                throw new \Exception("File failed to generate at $outPath");
            }

            return response()->download($outPath, $downloadName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membuat dokumen: ' . $e->getMessage());
        }
    }

    public function previewEmail(Request $request, SuratJenis $suratJenis, SuratTemplate $template, Surat $surat, SuratTemplateService $service)
    {
        $fields = $service->buildFields($surat);
        $subjectTpl = $template->email_subject_template ?: 'Surat ' . ($suratJenis->nama ?? '');
        $bodyTpl = $template->email_body_template ?: "Yth. Penerima,\n\nBerikut kami lampirkan surat yang dimohonkan.\n";

        $subject = $this->interpolate($subjectTpl, $fields);
        $body = $this->interpolate($bodyTpl, $fields);

        return response()->json([
            'subject' => $subject,
            'body' => $body,
        ]);
    }

    public function sendEmail(Request $request, SuratJenis $suratJenis, SuratTemplate $template, Surat $surat, SuratTemplateService $service)
    {
        abort_unless((int) $template->surat_jenis_id === (int) $suratJenis->id, 404);
        abort_unless((int) $surat->surat_jenis_id === (int) $suratJenis->id, 404);

        $request->validate([
            'to' => 'required|email',
        ]);

        $outDir = base_path('uploads/documents/surat/generated');
        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }
        $filename = 'surat_' . $surat->id . '_' . time() . '.docx';
        $outPath = $outDir . '/' . $filename;
        $service->generateDocx($template, $surat, $outPath);

        $fields = $service->buildFields($surat);
        
        // Use subject/body from request if exists (for preview modal edits), otherwise use template
        $subject = $request->input('subject') ?: $this->interpolate($template->email_subject_template ?: 'Surat ' . ($suratJenis->nama ?? ''), $fields);
        $body = $request->input('body') ?: $this->interpolate($template->email_body_template ?: "Yth. Penerima,\n\nBerikut kami lampirkan surat yang dimohonkan.\n", $fields);

        $to = $request->input('to');

        Mail::raw($body, function ($message) use ($to, $subject, $outPath, $filename) {
            $message->to($to)->subject($subject)->attach($outPath, [
                'as' => $filename,
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
        });

        $surat->update([
            'penerima_email' => $to,
            'status' => 'dikirim',
            'sent_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Email berhasil dikirim.');
    }

    private function interpolate(string $tpl, array $fields): string
    {
        $out = $tpl;
        foreach ($fields as $key => $val) {
            $out = str_replace(['{{' . $key . '}}', '${' . $key . '}'], (string) $val, $out);
        }
        return $out;
    }
}
