<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\JabatanMitra;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;
use App\Models\SatuanKegiatan;
use App\Models\Honorarium;

class JabatanMitraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass Trigger SQLite untuk Subkegiatan (dibutuhkan untuk tes 409)
        Subkegiatan::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'sub_' . mt_rand(1000, 9999);
            }
        });
    }

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_jabatan',
            'email' => 'adminjabatan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
    }

    /**
     * PENGUJIAN: store() -> Sukses dan Gagal (Duplikat)
     */
    public function test_tambah_jabatan_sukses_dan_gagal_karena_kode_duplikat()
    {
        $this->authenticateAdmin();

        // 1. Sukses menambah jabatan pertama
        $response1 = $this->postJson('/api/jabatan', [
            'kode_jabatan' => 'PCL',
            'nama_jabatan' => 'Pencacah Lapangan'
        ]);

        $response1->assertStatus(201)
                  ->assertJsonPath('status', 'success');
        
        $this->assertDatabaseHas('jabatan_mitra', ['kode_jabatan' => 'PCL']);

        // 2. Gagal menambah jabatan dengan kode yang sama (PCL)
        $response2 = $this->postJson('/api/jabatan', [
            'kode_jabatan' => 'PCL', // Sengaja diduplikat
            'nama_jabatan' => 'Jabatan Lain'
        ]);

        // Ekspektasi: Ditolak dengan status 422
        $response2->assertStatus(422)
                  ->assertJsonStructure(['errors' => ['kode_jabatan']]);
    }

    /**
     * PENGUJIAN: update() -> Validasi dan Sukses
     */
    public function test_update_jabatan_gagal_validasi_dan_berhasil()
    {
        $this->authenticateAdmin();

        $jabatan = JabatanMitra::create([
            'kode_jabatan' => 'PML',
            'nama_jabatan' => 'Pengawas Lapangan'
        ]);

        // 1. Tes Update Gagal (nama_jabatan dikosongkan)
        $failResponse = $this->putJson('/api/jabatan/' . $jabatan->kode_jabatan, [
            'nama_jabatan' => '' 
        ]);
        $failResponse->assertStatus(422)
                     ->assertJsonStructure(['errors' => ['nama_jabatan']]);

        // 2. Tes Update Sukses
        $successResponse = $this->putJson('/api/jabatan/' . $jabatan->kode_jabatan, [
            'nama_jabatan' => 'Pengawas Lapangan Senior'
        ]);
        $successResponse->assertStatus(200)
                        ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('jabatan_mitra', [
            'kode_jabatan' => 'PML',
            'nama_jabatan' => 'Pengawas Lapangan Senior'
        ]);
    }

    /**
     * PENGUJIAN: show() dan destroy() -> Not Found (404)
     */
    public function test_show_dan_hapus_jabatan_gagal_karena_tidak_ditemukan_404()
    {
        $this->authenticateAdmin();

        // Tes Show ID ngawur
        $responseShow = $this->getJson('/api/jabatan/KODE_PALSU');
        $responseShow->assertStatus(404)
                     ->assertJsonPath('message', 'Jabatan tidak ditemukan');

        // Tes Delete ID ngawur
        $responseDelete = $this->deleteJson('/api/jabatan/KODE_PALSU');
        $responseDelete->assertStatus(404)
                       ->assertJsonPath('message', 'Jabatan tidak ditemukan');
    }

    /**
     * PENGUJIAN: destroy() -> Skenario Konflik Relasi (409)
     */
    public function test_hapus_jabatan_ditolak_karena_dipakai_di_honorarium_409()
    {
        $this->authenticateAdmin();

        // PENTING: Aktifkan pengecekan Foreign Key untuk database SQLite saat testing
        DB::statement('PRAGMA foreign_keys=on;');

        // 1. Buat Jabatan Induk
        $jabatan = JabatanMitra::create([
            'kode_jabatan' => 'KOR',
            'nama_jabatan' => 'Koordinator'
        ]);

        // 2. Buat Kegiatan, Subkegiatan, dan SATUAN (Syarat Honorarium)
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan X']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub X']);
        
        // PERBAIKAN: Buat satuan palsu agar tidak kena Not Null Constraint
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Dokumen', 'alias' => 'DOK']);

        // 3. Masukkan jabatan 'KOR' ke dalam tabel honorarium
        Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'id_satuan'      => $satuan->id, // ID satuan dimasukkan ke sini
            'tarif'          => 100000,
            'basis_volume'   => 1
        ]);

        // 4. Coba hapus Jabatan 'KOR'
        $response = $this->deleteJson('/api/jabatan/' . $jabatan->kode_jabatan);

        // Ekspektasi: Sistem masuk ke blok catch (\Exception $e) dan mereturn 409
        $response->assertStatus(409)
                 ->assertJsonPath('message', 'Gagal menghapus. Jabatan ini mungkin sedang digunakan di data Honorarium.');

        // Pastikan jabatan tidak jadi terhapus
        $this->assertDatabaseHas('jabatan_mitra', ['kode_jabatan' => 'KOR']);
    }

    /**
     * PENGUJIAN: destroy() -> Sukses (Jalur Bahagia)
     */
    public function test_hapus_jabatan_sukses_tanpa_konflik()
    {
        $this->authenticateAdmin();

        $jabatan = JabatanMitra::create([
            'kode_jabatan' => 'BEBAS',
            'nama_jabatan' => 'Jabatan Bebas Tugas'
        ]);

        // Jabatan ini aman dihapus karena tidak dipakai di tabel honorarium manapun
        $response = $this->deleteJson('/api/jabatan/' . $jabatan->kode_jabatan);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Jabatan berhasil dihapus');

        $this->assertDatabaseMissing('jabatan_mitra', ['kode_jabatan' => 'BEBAS']);
    }
}