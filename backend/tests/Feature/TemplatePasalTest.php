<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MasterTemplateSPK;
use App\Models\TemplatePasal;

class TemplatePasalTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_pasal',
            'email' => 'admin_pasal@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    public function test_store_pasal_berhasil()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template Kontrak']);

        $payload = [
            'template_id' => $template->id,
            'nomor_pasal' => '1',
            'judul_pasal' => 'Ruang Lingkup',
            'isi_pasal'   => 'Pihak pertama memberikan tugas...',
            'urutan'      => 1
        ];

        $response = $this->postJson('/api/template-pasal', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('template_pasal', ['judul_pasal' => 'Ruang Lingkup']);
    }

    public function test_delete_pasal_berhasil()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template Kontrak']);
        $pasal = TemplatePasal::create([
            'template_id' => $template->id,
            'nomor_pasal' => '2',
            'judul_pasal' => 'Honorarium',
            'isi_pasal'   => 'Dibayar per bulan.',
            'urutan'      => 2
        ]);

        $response = $this->deleteJson("/api/template-pasal/{$pasal->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('template_pasal', ['id' => $pasal->id]);
    }
}