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
            'username' => 'admin_perencanaan',
            'email' => 'admin_plan@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    /**
     * TEST 1: Simpan Pengaturan SPK Baru
     */
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

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('spk_setting', [
            'periode'  => '2026-01',
            'nama_ppk' => 'Budi Santoso'
        ]);
    }

    /**
     * TEST 2: Update Pengaturan SPK
     */
    public function test_update_spk_setting_berhasil()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template Default', 'is_active' => true]);

        $setting = SpkSetting::create([
            'periode'            => '2025-01',
            'template_id'        => $template->id,
            'nama_ppk'           => 'Lama',
            'nip_ppk'            => '123'
        ]);

        $payload = [
            'nama_ppk' => 'PPK Baru Update',
            'nip_ppk'  => '99999999'
        ];

        $response = $this->putJson("/api/spk-setting/{$setting->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('spk_setting', [
            'id'       => $setting->id,
            'nama_ppk' => 'PPK Baru Update'
        ]);
    }

    /**
     * TEST 3: Get By Periode
     */
    public function test_get_setting_by_periode_berhasil()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template 2024', 'is_active' => true]);

        SpkSetting::create([
            'periode'            => '2024-12',
            'template_id'        => $template->id,
            'nama_ppk'           => 'PPK 2024',
            'nip_ppk'            => '123'
        ]);

        $response = $this->getJson('/api/spk-setting/periode/2024-12');

        $response->assertStatus(200)
                 ->assertJsonPath('data.periode', '2024-12');
    }

    /**
     * TEST 4: Validasi Unik Periode
     */
    public function test_gagal_store_jika_periode_sudah_ada()
    {
        $this->authenticateAdmin();
        $template = MasterTemplateSPK::create(['nama_template' => 'Template Default', 'is_active' => true]);

        SpkSetting::create([
            'periode'            => '2026-12',
            'template_id'        => $template->id,
            'nama_ppk'           => 'A',
            'nip_ppk'            => '1'
        ]);

        $payload = [
            'periode'            => '2026-12',
            'template_id'        => $template->id,
            'nama_ppk'           => 'B', 
            'nip_ppk'            => '2'
        ];

        $response = $this->postJson('/api/spk-setting', $payload);

        // Perbaikan: Controller mengembalikan 409 (Conflict) untuk pengecekan periode manual
        $response->assertStatus(409);
    }
}