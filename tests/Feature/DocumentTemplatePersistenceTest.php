<?php

use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use setasign\Fpdi\Fpdi;

it('keeps uploaded templates and their field mappings available after reopening', function () {
    Storage::fake('public');
    $dean = User::factory()->create(['role' => 'Dean']);
    $pdf = new Fpdi;
    $pdf->AddPage();
    $bytes = $pdf->Output('S');
    $this->actingAs($dean)->postJson('/api/document-templates', [
        'name' => 'Endorsement Form (Prefinal)', 'file' => UploadedFile::fake()->createWithContent('endorsement.pdf', $bytes),
    ])->assertOk();
    $template = DocumentTemplate::firstOrFail();
    Storage::disk('public')->assertExists($template->file_path);
    $this->get('/api/document-templates/'.$template->id.'/preview')->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertStreamedContent($bytes);
    $fields = [['id' => 'name-map', 'key' => 'student.full_name', 'type' => 'text', 'page' => 1, 'x' => 40, 'y' => 60, 'width' => 200, 'height' => 30, 'font_size' => 11]];
    $this->putJson('/api/document-templates/'.$template->id.'/fields', ['fields' => $fields, 'fields_meta' => ['canvas_width' => 625, 'canvas_height' => 884]])->assertOk();
    $this->getJson('/api/document-templates/'.$template->id)->assertOk()->assertJsonPath('fields.0.key', 'student.full_name')->assertJsonPath('fields.0.x', 40)->assertJsonPath('fields_meta.canvas_width', 625);
    $this->actingAs(User::factory()->create(['role' => 'Student']))->get('/api/document-templates/'.$template->id.'/preview')->assertForbidden();
    $this->putJson('/api/document-templates/'.$template->id.'/fields', ['fields' => []])->assertForbidden();
});

it('reports missing template files instead of opening a broken storage link', function () {
    Storage::fake('public');
    $dean = User::factory()->create(['role' => 'Dean']);
    $template = DocumentTemplate::create(['name' => 'Missing', 'code' => 'missing', 'file_path' => 'templates/missing.pdf', 'created_by' => $dean->id]);
    $this->actingAs($dean)->get('/api/document-templates/'.$template->id.'/preview')->assertNotFound();
});

it('allows the dean to delete an unused PDF and mapping but denies other roles', function () {
    Storage::fake('public');
    $dean = User::factory()->create(['role' => 'Dean']);
    Storage::disk('public')->put('templates/delete.pdf', 'sample');
    $template = DocumentTemplate::create(['name' => 'Delete me', 'code' => 'delete-me', 'file_path' => 'templates/delete.pdf', 'created_by' => $dean->id, 'fields' => []]);
    foreach (['Student', 'Adviser', 'Coordinator', 'Administrative Assistant'] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))->deleteJson('/api/document-templates/'.$template->id)->assertForbidden();
    }
    Storage::disk('public')->assertExists($template->file_path);
    $this->actingAs($dean)->deleteJson('/api/document-templates/'.$template->id)->assertOk();
    $this->assertDatabaseMissing('document_templates', ['id' => $template->id]);
    Storage::disk('public')->assertMissing($template->file_path);
});

it('accepts an editable Word template and lets students download the saved original', function () {
    $this->withoutVite();
    Storage::fake('public');
    $dean = User::factory()->create(['role' => 'Dean']);
    $student = User::factory()->create(['role' => 'Student']);
    $file = tempnam(sys_get_temp_dir(), 'word-template-');
    $zip = new ZipArchive;
    $zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
    $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Blank endorsement form</w:t></w:r></w:p></w:body></w:document>');
    $zip->close();
    $bytes = file_get_contents($file);
    unlink($file);
    $this->actingAs($dean)->postJson('/api/document-templates', [
        'name' => 'Endorsement Form (Proposal)', 'file' => UploadedFile::fake()->createWithContent('form.docx', $bytes),
    ])->assertOk()->assertJsonPath('file_type', 'docx');
    $template = DocumentTemplate::firstOrFail();
    Storage::disk('public')->assertExists($template->file_path);
    $this->get('/api/document-templates/'.$template->id.'/preview')->assertStatus(422);
    $this->putJson('/api/document-templates/'.$template->id.'/fields', ['fields' => []])->assertUnprocessable();
    $this->actingAs($student)->get('/student/documents')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
        ->where('templates.0.file_type', 'docx')->where('templates.0.download_url', '/api/document-templates/'.$template->id.'/download'));
    $this->get('/api/document-templates/'.$template->id.'/download')->assertOk()
        ->assertDownload('endorsement-form-proposal.docx')->assertStreamedContent($bytes);
    $this->postJson('/api/document-templates', ['name' => 'No', 'file' => UploadedFile::fake()->createWithContent('form.docx', $bytes)])->assertForbidden();
});

it('rejects unsupported and renamed files and requires login for template downloads', function () {
    Storage::fake('public');
    $dean = User::factory()->create(['role' => 'Dean']);
    $this->actingAs($dean)->postJson('/api/document-templates', ['name' => 'Bad', 'file' => UploadedFile::fake()->createWithContent('bad.docx', 'ordinary text')])->assertUnprocessable();
    $template = DocumentTemplate::create(['name' => 'Form', 'code' => 'form', 'file_path' => 'templates/form.pdf', 'created_by' => $dean->id]);
    Storage::disk('public')->put($template->file_path, '%PDF-test');
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/document-templates/'.$template->id.'/download')->assertUnauthorized();
});
