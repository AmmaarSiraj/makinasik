<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MasterTemplateSPK;
use App\Models\TemplateBagianTeks;

class TemplateBagianTeksTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    /**
     * TEST: Simpan Bagian Teks (Store/Update)
     * Menggunakan route /api/template-bagian-teks sesuai api.php
     */
    public function test_store_atau_update_bagian_teks_berhasil()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template Dasar']);

        // Data untuk store baru
        $payload = [
            'template_id'  => $template->id,
            'jenis_bagian' => 'pembuka',
            'isi_teks'     => 'Dengan ini menyatakan...'
        ];

        // Perbaikan URL: Gunakan /api/template-bagian-teks sesuai routes/api.php
        $response = $this->postJson('/api/template-bagian-teks', $payload);

        $response->assertStatus(200) // Biasanya storeOrUpdate mengembalikan 200 atau 201
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('template_bagian_teks', [
            'template_id'  => $template->id,
            'jenis_bagian' => 'pembuka',
            'isi_teks'     => 'Dengan ini menyatakan...'
        ]);

        // Uji Update (karena fungsi di controller adalah storeOrUpdate)
        $payloadUpdate = [
            'template_id'  => $template->id,
            'jenis_bagian' => 'pembuka',
            'isi_teks'     => 'Teks Pembuka Diperbarui'
        ];

        $responseUpdate = $this->postJson('/api/template-bagian-teks', $payloadUpdate);
        $responseUpdate->assertStatus(200);

        $this->assertDatabaseHas('template_bagian_teks', [
            'template_id'  => $template->id,
            'jenis_bagian' => 'pembuka',
            'isi_teks'     => 'Teks Pembuka Diperbarui'
        ]);
    }
}