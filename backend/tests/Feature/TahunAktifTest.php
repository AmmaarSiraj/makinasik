<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mitra;
use App\Models\TahunAktif;

class TahunAktifTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin()
    {
        // Buat admin untuk melewati middleware (jika ada)
        $admin = User::create([
            'username' => 'admin_sistem',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin' // Gunakan role yang valid di tabel user
        ]);
        $this->actingAs($admin, 'sanctum');
    }

    /**
     * PENGUJIAN 1: Store Tahun Aktif Baru dan Validasi Duplikat (409)
     */
    public function test_tambah_tahun_aktif_sukses_dan_gagal_jika_tahun_sama()
    {
        $this->authenticateAdmin();

        // 1. Buat Data Mitra sebagai induk
        $mitra = Mitra::create([
            'nama_lengkap' => 'Budi Santoso',
            'nik' => '33101234567890',
            'nomor_hp' => '08123456789'
        ]);

        // 2. Tembak endpoint untuk mendaftarkan Budi di tahun 2026
        // Asumsi rute: POST /api/tahun-aktif
        $response1 = $this->postJson('/api/tahun-aktif', [
            'user_id' => $mitra->id, // field di form bernama user_id, tapi nyambung ke tabel mitra
            'tahun' => '2026',
            'status' => 'aktif'
        ]);

        $response1->assertStatus(201)
                  ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('tahun_aktif', [
            'user_id' => $mitra->id,
            'tahun' => '2026'
        ]);

        // 3. Tembak lagi dengan tahun yang sama (2026)
        $response2 = $this->postJson('/api/tahun-aktif', [
            'user_id' => $mitra->id,
            'tahun' => '2026', // Duplikat!
            'status' => 'non-aktif'
        ]);

        // Ekspektasi: Controller melempar 409 Conflict
        $response2->assertStatus(409)
                  ->assertJsonPath('message', 'Tahun ini sudah terdaftar untuk mitra tersebut');
    }

    /**
     * PENGUJIAN 2: Index (Filter berdasarkan ID Mitra)
     */
    public function test_ambil_tahun_aktif_berdasarkan_filter_mitra_id()
    {
        $this->authenticateAdmin();

        $mitraA = Mitra::create(['nama_lengkap' => 'Mitra A', 'nik' => '111', 'nomor_hp' => '000']);
        $mitraB = Mitra::create(['nama_lengkap' => 'Mitra B', 'nik' => '222', 'nomor_hp' => '000']);

        TahunAktif::create(['user_id' => $mitraA->id, 'tahun' => '2025']);
        TahunAktif::create(['user_id' => $mitraA->id, 'tahun' => '2026']);
        TahunAktif::create(['user_id' => $mitraB->id, 'tahun' => '2026']);

        // Filter: Hanya ambil riwayat tahun aktif milik Mitra A
        // Asumsi rute: GET /api/tahun-aktif?user_id=...
        $response = $this->getJson('/api/tahun-aktif?user_id=' . $mitraA->id);

        $response->assertStatus(200);

        // Ekspektasi: Hasilnya harus 2 (karena Mitra A punya 2 tahun)
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * PENGUJIAN 3: Update dan Delete (CRUD Standar)
     */
    public function test_update_dan_hapus_tahun_aktif_berhasil()
    {
        $this->authenticateAdmin();

        $mitra = Mitra::create(['nama_lengkap' => 'Mitra C', 'nik' => '333', 'nomor_hp' => '000']);
        
        $tahunAktif = TahunAktif::create([
            'user_id' => $mitra->id, 
            'tahun' => '2025',
            'status' => 'aktif'
        ]);

        // 1. Tes Update (Ubah status jadi non-aktif)
        $responseUpdate = $this->putJson('/api/tahun-aktif/' . $tahunAktif->id, [
            'status' => 'non-aktif'
        ]);

        $responseUpdate->assertStatus(200)
                       ->assertJsonPath('data.status', 'non-aktif');

        // 2. Tes Delete
        $responseDelete = $this->deleteJson('/api/tahun-aktif/' . $tahunAktif->id);

        $responseDelete->assertStatus(200)
                       ->assertJsonPath('message', 'Data berhasil dihapus');

        $this->assertDatabaseMissing('tahun_aktif', ['id' => $tahunAktif->id]);
    }

    /**
     * PENGUJIAN 4: Pengecekan Relasi Eager Loading ('mitra')
     */
    public function test_index_dan_show_mengembalikan_relasi_mitra()
    {
        $this->authenticateAdmin();

        $mitra = Mitra::create(['nama_lengkap' => 'Joko Anwar', 'nik' => '888', 'nomor_hp' => '000']);
        $tahun = TahunAktif::create(['user_id' => $mitra->id, 'tahun' => '2027']);

        // Di kode Controller Anda, terdapat: TahunAktif::with('mitra')
        // Kita buktikan apakah objek 'mitra' ikut terkirim dalam JSON

        $response = $this->getJson('/api/tahun-aktif/' . $tahun->id);

        $response->assertStatus(200)
                 ->assertJsonPath('data.mitra.nama_lengkap', 'Joko Anwar');
    }
}