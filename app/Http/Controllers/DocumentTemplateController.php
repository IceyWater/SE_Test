<?php

namespace App\Http\Controllers;

use App\Models\DefenseRequest;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Services\DocumentGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Fpdi;

class DocumentTemplateController extends Controller
{
    private function authorizeManagement(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['Dean', 'Super Admin']), 403);
    }

    public function index()
    {
        $list = DocumentTemplate::orderBy('created_at', 'desc')->get();

        return response()->json($list);
    }

    public function show(DocumentTemplate $template)
    {
        return response()->json($template);
    }

    public function download(DocumentTemplate $template)
    {
        abort_unless(auth()->check(), 401);
        $path = ltrim(preg_replace('#^/?storage/#', '', $template->file_path), '/');
        abort_unless(str_starts_with($path, 'templates/') && ! in_array('..', explode('/', $path), true), 404);
        $disk = Storage::disk('public');
        abort_unless(in_array($template->file_type, ['pdf', 'doc', 'docx'], true) && $disk->exists($path), 404, 'The template file is missing. Ask the Dean to upload it again.');
        $types = ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        return $disk->download($path, (\Str::slug($template->name) ?: 'template').'.'.$template->file_type, [
            'Content-Type' => $types[$template->file_type], 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ]);
    }

    public function preview(DocumentTemplate $template)
    {
        $this->authorizeManagement();
        abort_unless($template->file_type === 'pdf', 422, 'Word templates can be downloaded and edited. Field mapping is available for PDFs only.');
        $path = ltrim(preg_replace('#^/?storage/#', '', $template->file_path), '/');
        abort_unless(str_starts_with($path, 'templates/') && ! in_array('..', explode('/', $path), true), 404);
        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404, 'The template PDF is missing. Upload the template again.');

        return $disk->response($path, 'template.pdf', [
            'Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    public function store(Request $r)
    {
        $this->authorizeManagement();
        Log::info('DocumentTemplateController@store called', ['user_id' => $r->user()->id]);
        $data = $r->validate([
            'name' => 'required|string',
            'file' => 'required|file|mimes:pdf,doc,docx|extensions:pdf,doc,docx|max:10240',
        ]);
        $extension = strtolower($r->file('file')->getClientOriginalExtension());
        if ($extension === 'docx') {
            $zip = new \ZipArchive;
            $valid = $zip->open($r->file('file')->getRealPath()) === true;
            if ($valid) {
                $valid = $zip->locateName('[Content_Types].xml') !== false && $zip->locateName('word/document.xml') !== false;
                $zip->close();
            }
            if (! $valid) {
                throw ValidationException::withMessages(['file' => 'Upload a valid Word (.docx) document. Renaming another file does not convert it to Word.']);
            }
        }
        if ($extension === 'doc' && file_get_contents($r->file('file')->getRealPath(), false, null, 0, 8) !== hex2bin('d0cf11e0a1b11ae1')) {
            throw ValidationException::withMessages(['file' => 'Upload a valid Word (.doc) document, or save it as .docx.']);
        }
        $pageCount = 0;
        if (strtolower($r->file('file')->getClientOriginalExtension()) === 'pdf') {
            try {
                $parser = new Fpdi;
                $pageCount = $parser->setSourceFile($r->file('file')->getRealPath());
            } catch (\Throwable $error) {
                throw ValidationException::withMessages(['file' => 'Upload a readable, unencrypted PDF template.']);
            }
        }
        $code = \Str::slug($data['name']).'-'.uniqid();
        $path = $r->file('file')->storeAs('templates', \Str::uuid().'.'.$extension, 'public');
        try {
            $tpl = DocumentTemplate::create([
                'name' => $data['name'],
                'code' => $code,
                'file_path' => $path,
                'page_count' => $pageCount,
                'created_by' => $r->user()->id,
            ]);
        } catch (\Throwable $error) {
            Storage::disk('public')->delete($path);
            throw $error;
        }

        return response()->json($tpl);
    }

    public function updateFields(Request $request, DocumentTemplate $template)
    {
        $this->authorizeManagement();
        if ($template->file_type !== 'pdf') {
            throw ValidationException::withMessages(['template' => 'Field mapping is available for PDF templates only. Download the Word template to edit it.']);
        }
        $data = $request->validate([
            'fields' => 'present|array|max:300',
            'fields.*.key' => 'required|string|max:120|regex:/^[a-zA-Z0-9_.]+$/',
            'fields.*.type' => 'required|in:text,multiline,signature',
            'fields.*.page' => 'required|integer|min:1|max:1000',
            'fields.*.x' => 'required|numeric|min:0|max:20000',
            'fields.*.y' => 'required|numeric|min:0|max:20000',
            'fields.*.width' => 'required|numeric|gt:0|max:20000',
            'fields.*.height' => 'required|numeric|gt:0|max:20000',
            'fields.*.font_size' => 'sometimes|numeric|between:4,72',
            'fields_meta' => 'sometimes|array',
            'fields_meta.canvas_width' => 'required_with:fields_meta|numeric|gt:0|max:20000',
            'fields_meta.canvas_height' => 'required_with:fields_meta|numeric|gt:0|max:20000',
        ]);
        $template->fields = $data['fields'];
        $template->fields_meta = $data['fields_meta'] ?? [];

        $template->save();

        return response()->json(['ok' => true]);
    }

    public function destroy(DocumentTemplate $template)
    {
        $this->authorizeManagement();
        if (GeneratedDocument::where('document_template_id', $template->id)->exists()) {
            throw ValidationException::withMessages(['template' => 'This template has generated documents and cannot be deleted because they must remain accessible.']);
        }
        Storage::disk('public')->delete($template->file_path);
        $template->delete();

        return response()->json(['ok' => true]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|integer|exists:document_templates,id',
            'defense_request_id' => 'required|integer|exists:defense_requests,id',
            'fields' => 'array',
        ]);

        $template = DocumentTemplate::findOrFail($request->template_id);
        $defenseRequest = DefenseRequest::findOrFail($request->defense_request_id);

        $user = $request->user();
        abort_unless($user && (in_array($user->role, ['Dean', 'Super Admin'], true)
            || ($user->role === 'Adviser' && (int) $defenseRequest->adviser_user_id === (int) $user->id)), 403);

        // You may want to merge $request->fields into the defenseRequest data for the generator
        $generator = new DocumentGenerator;
        try {
            $generated = $generator->generate($template, $defenseRequest, $request->fields ?? []);
        } catch (\Throwable $e) {
            \Log::error('Document generation failed: '.$e->getMessage());

            return response()->json([
                'ok' => false,
                'error' => 'Document generation failed: '.$e->getMessage(),
            ], 500);
        }

        // Assume $generated is a GeneratedDocument model with output_path
        $url = $generated->output_path
            ? \Storage::disk('public')->url($generated->output_path)
            : null;

        return response()->json([
            'ok' => true,
            'download_url' => $url,
        ]);
    }
}
