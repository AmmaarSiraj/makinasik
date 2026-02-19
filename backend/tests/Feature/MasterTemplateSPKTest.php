<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MasterTemplateSPK;
use App\Models\TemplateBagianTeks;
use App\Models\TemplatePasal;

class MasterTemplateSPKTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_perencanaan',
            'email' => 'admin_plan@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    /**
     * TEST 1: Buat Template Baru
     */
    public function test_store_template_spk_berhasil()
    {
        $this->authenticateAdmin();

        $payload = [
            'nama_template' => 'Template Perjanjian Kerja 2026',
            'is_active'     => true // Meskipun dikirim true, controller menindasnya menjadi false
        ];

        $response = $this->postJson('/api/template-spk', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        // Perbaikan: is_active harus false sesuai logika MasterTemplateSPKController@store
        $this->assertDatabaseHas('master_template_spk', [
            'nama_template' => 'Template Perjanjian Kerja 2026',
            'is_active'     => false 
        ]);
    }
    public function test_index_template_spk_berhasil()
    {
        $this->authenticateAdmin();
        MasterTemplateSPK::create(['nama_template' => 'Template A']);
        MasterTemplateSPK::create(['nama_template' => 'Template B']);

        $response = $this->getJson('/api/template-spk');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }

    /**
     * TEST: Update Template Lengkap (Meningkatkan Coverage Update)
     * Menguji pembaruan Nama, Bagian Teks, dan Pasal sekaligus.
     */
    public function test_update_template_lengkap_berhasil()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template Awal']);

        $payload = [
            'nama_template' => 'Template Diperbarui',
            'parts' => [
                'pembuka' => 'Teks pembuka baru',
                'penutup' => 'Teks penutup baru'
            ],
            'articles' => [
                [
                    'nomor_pasal' => '1',
                    'judul_pasal' => 'Pasal Baru',
                    'isi_pasal'   => 'Isi pasal baru'
                ]
            ]
        ];

        $response = $this->putJson("/api/template-spk/{$template->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonPath('data.nama_template', 'Template Diperbarui');

        // Pastikan data tersimpan di database
        $this->assertDatabaseHas('master_template_spk', ['nama_template' => 'Template Diperbarui']);
        $this->assertDatabaseHas('template_bagian_teks', ['isi_teks' => 'Teks pembuka baru']);
        $this->assertDatabaseHas('template_pasal', ['judul_pasal' => 'Pasal Baru']);
    }

    /**
     * TEST 2: Fitur Set Active (Toggle)
     */
    public function test_set_active_menonaktifkan_template_lain()
    {
        $this->authenticateAdmin();

        $template1 = MasterTemplateSPK::create(['nama_template' => 'Template Lama', 'is_active' => true]);
        $template2 = MasterTemplateSPK::create(['nama_template' => 'Template Baru', 'is_active' => false]);

        $response = $this->putJson("/api/template-spk/{$template2->id}/set-active");

        $response->assertStatus(200);

        $this->assertDatabaseHas('master_template_spk', ['id' => $template1->id, 'is_active' => false]);
        $this->assertDatabaseHas('master_template_spk', ['id' => $template2->id, 'is_active' => true]);
    }

    /**
     * TEST 3: Get Detail Template beserta Relasi
     */
    public function test_show_template_mengembalikan_relasi_teks_dan_pasal()
    {
        $this->authenticateAdmin();

        $template = MasterTemplateSPK::create(['nama_template' => 'Template Lengkap', 'is_active' => true]);
        
        // Perbaikan: Gunakan 'template_id' sesuai definisi di model dan migrasi
        TemplateBagianTeks::create([
            'template_id' => $template->id, 
            'jenis_bagian' => 'pembuka',
            'isi_teks' => 'Ini adalah pembuka.'
        ]);

        TemplatePasal::create([
            'template_id' => $template->id,
            'nomor_pasal' => '1',
            'judul_pasal' => 'Definisi',
            'isi_pasal' => 'Pasal definisi.',
            'urutan' => 1
        ]);

        $response = $this->getJson("/api/template-spk/{$template->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'nama_template',
                         'bagian_teks', 
                         'pasal'        
                     ]
                 ]);
        
        $this->assertEquals('Ini adalah pembuka.', $response->json('data.bagian_teks.0.isi_teks'));
    }

    /**
     * TEST 4: Hapus Template
     */
    public function test_destroy_template_berhasil()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template Hapus', 'is_active' => false]);

        $response = $this->deleteJson("/api/template-spk/{$template->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('master_template_spk', ['id' => $template->id]);
    }
}