<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mitra;
use App\Models\TahunAktif;

class MitraTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_mitra',
            'email' => 'adminmitra@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
    }

    /**
     * PENGUJIAN FUNGSI: store()
     */
    public function test_tambah_mitra_sukses_dan_otomatis_aktif_tahun_ini()
    {
        $this->authenticateAdmin();

        $data = [
            'nama_lengkap' => 'Budi Santoso',
            'nik' => '3310123456789012',
            'nomor_hp' => '08123456789',
            'tahun_daftar' => '2026'
        ];

        $response = $this->postJson('/api/mitra', $data);

        // Ekspektasi 1: Status 201 Created
        $response->assertStatus(201)
                 ->assertJson(['status' => 'success']);

        // Ekspektasi 2: Masuk ke tabel mitra
        $this->assertDatabaseHas('mitra', ['nik' => '3310123456789012']);

        // Ekspektasi 3: Otomatis terdaftar di tabel tahun_aktif sesuai logika kode Anda
        $mitra = Mitra::where('nik', '3310123456789012')->first();
        $this->assertDatabaseHas('tahun_aktif', [
            'user_id' => $mitra->id,
            'tahun' => '2026',
            'status' => 'aktif'
        ]);
    }

    public function test_tambah_mitra_gagal_karena_validasi()
    {
        $this->authenticateAdmin();
        $response = $this->postJson('/api/mitra', []); // Kosong
        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['nama_lengkap', 'nik', 'nomor_hp']]);
    }

    /**
     * PENGUJIAN FUNGSI: index() & optimize()
     */
    public function test_ambil_semua_mitra_dengan_relasi_tahun()
    {
        $this->authenticateAdmin();

        // Buat dummy mitra dan tahun aktif
        $mitra = Mitra::create(['nama_lengkap' => 'Siti Aminah', 'nik' => '3310999', 'nomor_hp' => '08111']);
        TahunAktif::create(['user_id' => $mitra->id, 'tahun' => '2025', 'status' => 'aktif']);
        TahunAktif::create(['user_id' => $mitra->id, 'tahun' => '2026', 'status' => 'aktif']);

        // Test Endpoint Optimize (Pagination Server-side)
        $response = $this->getJson('/api/mitraop?year=2026');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('extra_meta.selected_year', '2026');

        // Pastikan kolom buatan 'riwayat_tahun' berhasil di-generate menjadi string "2026, 2025"
        $this->assertStringContainsString('2026', $response->json('data.data.0.riwayat_tahun'));
    }

    /**
     * PENGUJIAN FUNGSI: update()
     */
    public function test_update_mitra_gagal_jika_nik_sudah_dipakai_orang_lain()
    {
        $this->authenticateAdmin();

        Mitra::create(['nama_lengkap' => 'Mitra A', 'nik' => '111', 'nomor_hp' => '000']);
        $mitraB = Mitra::create(['nama_lengkap' => 'Mitra B', 'nik' => '222', 'nomor_hp' => '000']);

        // Mitra B mencoba pakai NIK Mitra A
        $response = $this->putJson('/api/mitra/' . $mitraB->id, [
            'nama_lengkap' => 'Mitra B Edit',
            'nik' => '111' 
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['nik']]);
    }

    /**
     * PENGUJIAN FUNGSI: destroy() (Cabang Hapus Permanen vs Hapus Tahun)
     */
    public function test_hapus_sebagian_tahun_aktif_saja()
    {
        $this->authenticateAdmin();
        $mitra = Mitra::create(['nama_lengkap' => 'Target', 'nik' => '999', 'nomor_hp' => '000']);
        
        // Dia aktif di 2 tahun berbeda
        TahunAktif::create(['user_id' => $mitra->id, 'tahun' => '2025', 'status' => 'aktif']);
        TahunAktif::create(['user_id' => $mitra->id, 'tahun' => '2026', 'status' => 'aktif']);

        // Request hapus hanya untuk tahun 2025
        $response = $this->deleteJson('/api/mitra/' . $mitra->id . '?tahun=2025');

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Status aktif tahun 2025 berhasil dihapus.');

        // Pastikan Mitranya TIDAK terhapus, hanya tahun 2025-nya saja
        $this->assertDatabaseHas('mitra', ['nik' => '999']);
        $this->assertDatabaseMissing('tahun_aktif', ['user_id' => $mitra->id, 'tahun' => '2025']);
    }

    /**
     * PENGUJIAN FUNGSI: import() (Upload CSV)
     */
    public function test_import_file_format_tidak_valid()
    {
        $this->authenticateAdmin();

        // Pura-pura upload file PDF (seharusnya ditolak karena wajib xlsx/csv)
        $file = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/mitra/import', ['file' => $file]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['file']]);
    }

    public function test_import_csv_berhasil_memasukkan_data()
    {
        $this->authenticateAdmin();

        // Kita buat simulasi file CSV di dalam memori untuk di-upload
        $csvContent = "Nama Lengkap,NIK,Sobat ID,No HP\nJoko Santoso,3310001,SBT-99,08123\nAndi,3310002,,08124";
        $file = UploadedFile::fake()->createWithContent('data_mitra.csv', $csvContent);

        $response = $this->postJson('/api/mitra/import', [
            'file' => $file,
            'tahun_daftar' => '2026'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('successCount', 2); // Karena ada 2 baris data di CSV tadi

        // Pastikan Joko Santoso benar-benar masuk database
        $this->assertDatabaseHas('mitra', ['nik' => '3310001', 'nama_lengkap' => 'Joko Santoso']);
    }

    /**
     * PENGUJIAN FUNGSI: getByPeriode()
     */
    public function test_get_by_periode_format_salah()
    {
        $this->authenticateAdmin();
        
        // Memasukkan format "2025" padahal kodenya minta "2025-12"
        $response = $this->getJson('/api/mitra/periode/2025');
        
        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Format periode salah');
    }
}