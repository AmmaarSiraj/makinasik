<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SpkSetting;
use App\Models\MasterTemplateSPK;

class SPKSettingTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_spk',
            'email' => 'admin_spk@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    /* ==============================================================
       1. PENGUJIAN FUNGSI INDEX
    ============================================================== */
    public function test_index_mengembalikan_semua_data_dan_filter_periode()
    {
        $this->authenticateAdmin();

        SpkSetting::create(['periode' => '2025-01', 'nama_ppk' => 'PPK 1']);
        SpkSetting::create(['periode' => '2025-02', 'nama_ppk' => 'PPK 2']);

        // Tanpa filter
        $responseAll = $this->getJson('/api/spk-setting');
        $responseAll->assertStatus(200)
                    ->assertJsonPath('status', 'success');
        $this->assertCount(2, $responseAll->json('data'));

        // Dengan filter periode
        $responseFilter = $this->getJson('/api/spk-setting?periode=2025-01');
        $responseFilter->assertStatus(200);
        $this->assertCount(1, $responseFilter->json('data'));
        $this->assertEquals('PPK 1', $responseFilter->json('data.0.nama_ppk'));
    }

    /* ==============================================================
       2. PENGUJIAN FUNGSI STORE
    ============================================================== */
    public function test_store_spk_setting_berhasil()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template Default', 'is_active' => true]);

        $payload = [
            'periode'            => '2026-01',
            'template_id'        => $template->id,
            'nomor_surat_format' => '{nomor}/BPS/2026',
            'tanggal_surat'      => '2026-01-02',
            'nama_ppk'           => 'Budi Santoso',
            'nip_ppk'            => '198001012000031001',
            'jabatan_ppk'        => 'Pejabat Pembuat Komitmen',
            'komponen_honor'     => 'Honorarium Petugas Lapangan'
        ];

        $response = $this->postJson('/api/spk-setting', $payload);

        $response->assertStatus(201)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('spk_setting', ['periode' => '2026-01', 'nama_ppk' => 'Budi Santoso']);
    }

    public function test_store_spk_setting_gagal_karena_validasi_dan_konflik()
    {
        $this->authenticateAdmin();

        // Uji Validasi (422) - periode kosong
        $responseVal = $this->postJson('/api/spk-setting', []);
        $responseVal->assertStatus(422)->assertJsonStructure(['errors' => ['periode']]);

        // Uji Konflik (409) - periode sudah ada
        SpkSetting::create(['periode' => '2026-12', 'nama_ppk' => 'A']);

        $responseCon = $this->postJson('/api/spk-setting', [
            'periode' => '2026-12',
            'nama_ppk' => 'B'
        ]);
        $responseCon->assertStatus(409)->assertJsonPath('message', 'Pengaturan SPK untuk periode ini sudah ada. Silakan edit data yang sudah ada.');
    }

    /* ==============================================================
       3. PENGUJIAN FUNGSI SHOW
    ============================================================== */
    public function test_show_spk_setting_berhasil_dan_not_found()
    {
        $this->authenticateAdmin();
        
        $setting = SpkSetting::create(['periode' => '2026-05', 'nama_ppk' => 'PPK Show']);

        // Sukses
        $response = $this->getJson('/api/spk-setting/' . $setting->id);
        $response->assertStatus(200)->assertJsonPath('data.nama_ppk', 'PPK Show');

        // Gagal (404)
        $resFail = $this->getJson('/api/spk-setting/9999');
        $resFail->assertStatus(404);
    }

    /* ==============================================================
       4. PENGUJIAN FUNGSI UPDATE
    ============================================================== */
    public function test_update_spk_setting_berhasil()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template Default', 'is_active' => true]);

        $setting = SpkSetting::create([
            'periode' => '2025-01',
            'template_id' => $template->id,
            'nama_ppk' => 'Lama'
        ]);

        $response = $this->putJson("/api/spk-setting/{$setting->id}", ['nama_ppk' => 'PPK Baru Update']);

        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('spk_setting', ['id' => $setting->id, 'nama_ppk' => 'PPK Baru Update']);
    }

    public function test_update_spk_setting_gagal_not_found_validasi_dan_konflik()
    {
        $this->authenticateAdmin();

        $settingA = SpkSetting::create(['periode' => '2025-05']);
        SpkSetting::create(['periode' => '2025-06']); // Periode untuk tes konflik

        // 1. Not Found (404)
        $res404 = $this->putJson('/api/spk-setting/9999', ['nama_ppk' => 'PPK Baru']);
        $res404->assertStatus(404);

        // 2. Validasi error (422) - misal template id tidak valid
        $res422 = $this->putJson('/api/spk-setting/' . $settingA->id, ['template_id' => 9999]);
        $res422->assertStatus(422);

        // 3. Konflik (409) - mengubah periode A menjadi periode B yang sudah ada
        $res409 = $this->putJson('/api/spk-setting/' . $settingA->id, ['periode' => '2025-06']);
        $res409->assertStatus(409)->assertJsonPath('message', 'Periode tersebut sudah digunakan di setting lain');
    }

    /* ==============================================================
       5. PENGUJIAN FUNGSI DESTROY
    ============================================================== */
    public function test_destroy_spk_setting_berhasil_dan_not_found()
    {
        $this->authenticateAdmin();
        $setting = SpkSetting::create(['periode' => '2026-10']);

        // Sukses hapus
        $response = $this->deleteJson('/api/spk-setting/' . $setting->id);
        $response->assertStatus(200)->assertJsonPath('message', 'Setting SPK berhasil dihapus');
        $this->assertDatabaseMissing('spk_setting', ['id' => $setting->id]);

        // Not Found
        $resFail = $this->deleteJson('/api/spk-setting/9999');
        $resFail->assertStatus(404);
    }

    /* ==============================================================
       6. PENGUJIAN FUNGSI GET BY PERIODE
    ============================================================== */
    public function test_get_setting_by_periode_berhasil_dan_not_found()
    {
        $this->authenticateAdmin();
        SpkSetting::create(['periode' => '2024-12', 'nama_ppk' => 'PPK 2024']);

        // Sukses
        $response = $this->getJson('/api/spk-setting/periode/2024-12');
        $response->assertStatus(200)->assertJsonPath('data.periode', '2024-12');

        // Not Found
        $resFail = $this->getJson('/api/spk-setting/periode/2020-01');
        $resFail->assertStatus(404)->assertJsonPath('message', 'Setting untuk periode ini belum diatur');
    }
}