<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\LegalDocument;
use App\Models\User;
use Database\Seeders\LegalDocumentSeeder;

beforeEach(function () {
    $this->seed(LegalDocumentSeeder::class);

    $this->superadmin = User::factory()->create([
        'name' => 'Superadmin Test',
        'role' => Role::Superadmin,
        'email_verified_at' => now(),
    ]);

    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'role' => Role::Admin,
        'email_verified_at' => now(),
    ]);
});

test('non-superadmin cannot access legal documents management', function () {
    $this->actingAs($this->admin)
        ->get(route('superadmin.legal-documents.index'))
        ->assertForbidden();
});

test('superadmin can view legal documents list', function () {
    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.legal-documents.index'));

    $response->assertOk()
        ->assertSee('Kelola Konten &amp; Dokumen Legal', false)
        ->assertSee('Standar Operasional Prosedur')
        ->assertSee('Versi 2.2');
});

test('superadmin can update legal document and bump version with audit trail', function () {
    $doc = LegalDocument::where('type', 'sop_kemitraan')->firstOrFail();

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.legal-documents.update', $doc), [
            'title' => 'SOP Kemitraan Terapis Terpadu',
            'version' => '2.3',
            'summary' => 'Penambahan regulasi higienitas baru',
            'content' => 'Teks lengkap baru SOP kemitraan terapis dengan klausul higienitas dan perlengkapan wajib.',
            'reason' => 'Pembaruan SOP berkala tahunan',
        ]);

    $response->assertRedirect(route('superadmin.legal-documents.index'))
        ->assertSessionHas('status');

    $doc->refresh();
    expect($doc->version)->toBe('2.3')
        ->and($doc->title)->toBe('SOP Kemitraan Terapis Terpadu')
        ->and($doc->updated_by)->toBe($this->superadmin->id);

    // Verify audit log
    $log = AuditLog::where('module', 'legal')->first();
    expect($log)->not->toBeNull()
        ->and($log->reason)->toBe('Pembaruan SOP berkala tahunan')
        ->and($log->new_values['version'])->toBe('2.3');
});
