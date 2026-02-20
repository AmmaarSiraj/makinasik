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
        return $admin;
    }

    /* ==============================================================
       PENGUJIAN FUNGSI INDEX & OPTIMIZE (DENGAN SEARCH)
    ============================================================== */
    public function test_ambil_semua_mitra_dengan_pencarian()
    {
        $this->authenticateAdmin();

        $mitra1 = Mitra::create(['nama_lengkap' => 'Ahmad Fulan', 'nik' => '111', 'sobat_id' => 'S1']);
        $mitra2 = Mitra::create(['nama_lengkap' => 'Budi Santoso', 'nik' => '222', 'sobat_id' => 'S2']);
        TahunAktif::create(['user_id' => $mitra1->id, 'tahun' => '2026', 'status' => 'aktif']);

        // 1. Tes Index biasa dengan search
        $responseIndex = $this->getJson('/api/mitra?search=Budi');
        $responseIndex->assertStatus(200);
        $this->assertEquals(1, count($responseIndex->json('data')));

        // 2. Tes Optimize dengan search
        $responseOpt = $this->getJson('/api/mitraop?year=2026&search=Ahmad');
        $responseOpt->assertStatus(200);
        $this->assertStringContainsString('Ahmad Fulan', $responseOpt->json('data.data.0.nama_lengkap'));
    }

    /* ==============================================================
       PENGUJIAN FUNGSI STORE
    ============================================================== */
    public function test_tambah_mitra_sukses_dan_otomatis_aktif_tahun_ini()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/mitra', [
            'nama_lengkap' => 'Budi Santoso',
            'nik' => '3310123456789012',
            'nomor_hp' => '08123456789',
            'tahun_daftar' => '2026'
        ]);

        $response->assertStatus(201)->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('mitra', ['nik' => '3310123456789012']);

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
        $response = $this->postJson('/api/mitra', []);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['nama_lengkap', 'nik', 'nomor_hp']]);
    }

    /* ==============================================================
       PENGUJIAN FUNGSI SHOW
    ============================================================== */
    public function test_lihat_detail_mitra_sukses_dan_gagal()
    {
        $this->authenticateAdmin();
        $mitra = Mitra::create(['nama_lengkap' => 'Tes Detail', 'nik' => '789', 'nomor_hp' => '080']);

        // Sukses
        $resSukses = $this->getJson('/api/mitra/' . $mitra->id);
        $resSukses->assertStatus(200)->assertJsonPath('data.nama_lengkap', 'Tes Detail');

        // Gagal 404
        $resGagal = $this->getJson('/api/mitra/9999');
        $resGagal->assertStatus(404);
    }

    /* ==============================================================
       PENGUJIAN FUNGSI UPDATE
    ============================================================== */
    public function test_update_mitra_sukses_gagal_dan_not_found()
    {
        $this->authenticateAdmin();

        $mitraA = Mitra::create(['nama_lengkap' => 'Mitra A', 'nik' => '111', 'nomor_hp' => '000']);
        $mitraB = Mitra::create(['nama_lengkap' => 'Mitra B', 'nik' => '222', 'nomor_hp' => '000']);

        // 1. Sukses Update
        $resSukses = $this->putJson('/api/mitra/' . $mitraA->id, [
            'nama_lengkap' => 'Mitra A Edit',
            'nik' => '111' 
        ]);
        $resSukses->assertStatus(200)->assertJsonPath('data.nama_lengkap', 'Mitra A Edit');

        // 2. Gagal Validasi Duplikat (B pakai NIK A)
        $resDup = $this->putJson('/api/mitra/' . $mitraB->id, [
            'nama_lengkap' => 'Mitra B',
            'nik' => '111' 
        ]);
        $resDup->assertStatus(422)->assertJsonStructure(['errors' => ['nik']]);

        // 3. Not Found
        $res404 = $this->putJson('/api/mitra/9999', ['nama_lengkap' => 'Z', 'nik' => '9']);
        $res404->assertStatus(404);
    }

    /* ==============================================================
       PENGUJIAN FUNGSI DESTROY
    ============================================================== */
    public function test_hapus_mitra_permanen_dan_not_found()
    {
        $this->authenticateAdmin();
        $mitra = Mitra::create(['nama_lengkap' => 'Target', 'nik' => '999', 'nomor_hp' => '000']);
        
        // Sengaja hanya punya 1 tahun aktif, maka akan dihapus permanen
        TahunAktif::create(['user_id' => $mitra->id, 'tahun' => '2026', 'status' => 'aktif']);

        // Hapus permanen
        $resHapus = $this->deleteJson('/api/mitra/' . $mitra->id);
        $resHapus->assertStatus(200)->assertJsonPath('message', 'Data mitra dihapus permanen.');
        $this->assertDatabaseMissing('mitra', ['id' => $mitra->id]);

        // Hapus Not Found
        $res404 = $this->deleteJson('/api/mitra/9999');
        $res404->assertStatus(404);
    }

    public function test_hapus_sebagian_tahun_aktif_saja()
    {
        $this->authenticateAdmin();
        $mitra = Mitra::create(['nama_lengkap' => 'Target 2', 'nik' => '888', 'nomor_hp' => '000']);
        
        TahunAktif::create(['user_id' => $mitra->id, 'tahun' => '2025', 'status' => 'aktif']);
        TahunAktif::create(['user_id' => $mitra->id, 'tahun' => '2026', 'status' => 'aktif']);

        $response = $this->deleteJson('/api/mitra/' . $mitra->id . '?tahun=2025');
        $response->assertStatus(200)->assertJsonPath('message', 'Status aktif tahun 2025 berhasil dihapus.');

        $this->assertDatabaseHas('mitra', ['nik' => '888']);
        $this->assertDatabaseMissing('tahun_aktif', ['user_id' => $mitra->id, 'tahun' => '2025']);
    }

    /* ==============================================================
       PENGUJIAN FUNGSI IMPORT EXCEL/CSV
    ============================================================== */
    public function test_import_file_format_tidak_valid()
    {
        $this->authenticateAdmin();
        $file = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');
        $response = $this->postJson('/api/mitra/import', ['file' => $file]);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['file']]);
    }

    public function test_import_csv_berhasil_memasukkan_data()
    {
        $this->authenticateAdmin();
        $csvContent = "Nama Lengkap,NIK,Sobat ID,No HP\nJoko Santoso,3310001,SBT-99,08123\nAndi,3310002,,08124";
        $file = UploadedFile::fake()->createWithContent('data_mitra.csv', $csvContent);

        $response = $this->postJson('/api/mitra/import', [
            'file' => $file,
            'tahun_daftar' => '2026'
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('mitra', ['nik' => '3310001', 'nama_lengkap' => 'Joko Santoso']);
    }

    /* ==============================================================
       PENGUJIAN FUNGSI GET BY PERIODE 
    ============================================================== */
    public function test_get_by_periode_format_salah()
    {
        $this->authenticateAdmin();
        
        // Memasukkan format "2025" padahal kodenya minta "2025-12"
        $response = $this->getJson('/api/mitra/periode/2025');
        
        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Format periode salah');
    }

    /* ==============================================================
       PENGUJIAN FUNGSI MITRA AKTIF (KHUSUS DASHBOARD)
    ============================================================== */
    public function test_mitra_aktif_mengembalikan_data_dengan_benar()
    {
        $this->authenticateAdmin();
        
        // Buat mitra dan set Tahun Aktif-nya (TIDAK ADA HUBUNGAN DENGAN PENUGASAN)
        $mitra = Mitra::create([
            'nama_lengkap' => 'Mitra Tahun Aktif', 
            'nik' => '123456789', 
            'nomor_hp' => '08123456'
        ]);
        
        TahunAktif::create([
            'user_id' => $mitra->id, 
            'tahun' => '2026', 
            'status' => 'aktif'
        ]);

        // Hit API menggunakan parameter tahun
        $response = $this->getJson('/api/mitra/aktif?tahun=2026');
        
        $response->assertStatus(200);
        $this->assertEquals('Mitra Tahun Aktif', $response->json('data.0.nama_lengkap'));
    }

    public function test_model_mitra_relation_berjalan_dengan_benar()
    {
        // 1. Buat data Mitra
        $mitra = Mitra::create([
            'nama_lengkap' => 'Mitra Uji Relasi',
            'nik' => '9999999999999999',
            'nomor_hp' => '08999999999'
        ]);

        // 2. Buat data relasi Tahun Aktif
        TahunAktif::create([
            'user_id' => $mitra->id,
            'tahun' => '2026',
            'status' => 'aktif'
        ]);

        // 3. Panggil relasi tahunAktif()
        $relasiTahun = $mitra->tahunAktif;
        
        // Ekspektasi: return value harus berupa Collection dan datanya cocok
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $relasiTahun);
        $this->assertEquals('2026', $relasiTahun->first()->tahun);

        // 4. Panggil relasi kelompokPenugasan()
        // Meskipun datanya tidak kita buat (kosong), memanggilnya sudah cukup untuk dihitung oleh coverage
        $relasiPenugasan = $mitra->kelompokPenugasan;
        
        // Ekspektasi: return value berupa Collection kosong
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $relasiPenugasan);
        $this->assertCount(0, $relasiPenugasan);
    }
}