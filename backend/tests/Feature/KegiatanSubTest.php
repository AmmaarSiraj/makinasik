<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;

class KegiatanSubTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SETUP: Dijalankan otomatis sebelum setiap test dimulai
     */
    protected function setUp(): void
    {
        parent::setUp();

        // SIMULASI TRIGGER MYSQL:
        // Karena SQLite tidak punya trigger, kita paksa Laravel untuk 
        // mengisikan ID secara otomatis sebelum data disimpan ke database.
        Subkegiatan::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'sub_' . mt_rand(1000, 9999);
            }
        });
    }

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_kegiatan',
            'email' => 'adminkegiatan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
    }

    /**
     * PENGUJIAN: KegiatanController@store dan show (Gagal 404)
     */
    public function test_tambah_kegiatan_sukses_dan_cek_not_found()
    {
        $this->authenticateAdmin();

        // 1. Tes Tambah Kegiatan
        $response = $this->postJson('/api/kegiatan', [
            'nama_kegiatan' => 'Sensus Ekonomi 2026',
            'deskripsi' => 'Kegiatan sensus skala nasional'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('kegiatan', ['nama_kegiatan' => 'Sensus Ekonomi 2026']);

        // 2. Tes Cabang Error (Show ID 999 yang tidak ada)
        $errorResponse = $this->getJson('/api/kegiatan/999');
        $errorResponse->assertStatus(404)
                      ->assertJsonPath('message', 'Kegiatan tidak ditemukan');
    }

    /**
     * PENGUJIAN: SubkegiatanController@store (Cabang Validasi Tanggal)
     */
    public function test_validasi_tanggal_subkegiatan_gagal_jika_mundur()
    {
        $this->authenticateAdmin();

        $kegiatan = Kegiatan::create([
            'nama_kegiatan' => 'Sensus Pertanian',
            'deskripsi' => '-'
        ]);

        // Skenario: Tanggal selesai (10 Mei) lebih awal dari Tanggal Mulai (15 Mei)
        $response = $this->postJson('/api/subkegiatan', [
            'id_kegiatan' => $kegiatan->id,
            'nama_sub_kegiatan' => 'Pencacahan Lapangan',
            'tanggal_mulai' => '2026-05-15',
            'tanggal_selesai' => '2026-05-10' 
        ]);

        // Ekspektasi: Validasi gagal (422) pada kolom tanggal_selesai
        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['tanggal_selesai']]);
    }

    /**
     * PENGUJIAN: KegiatanController@destroy (Cek Cascade Delete)
     */
    public function test_hapus_kegiatan_otomatis_menghapus_subkegiatan_di_bawahnya()
    {
        $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Induk']);
        
        // Kita tidak perlu memasukkan 'id' manual lagi, karena setUp() di atas akan mengisinya
        Subkegiatan::create([
            'id_kegiatan' => $kegiatan->id,
            'nama_sub_kegiatan' => 'Sub Anak 1',
        ]);

        // Hapus Kegiatan Induknya
        $response = $this->deleteJson('/api/kegiatan/' . $kegiatan->id);
        $response->assertStatus(200);

        // Ekspektasi: Kegiatan hilang, dan Sub Anak 1 juga harus HILANG dari database
        $this->assertDatabaseMissing('kegiatan', ['id' => $kegiatan->id]);
        $this->assertDatabaseMissing('subkegiatan', ['nama_sub_kegiatan' => 'Sub Anak 1']);
    }

    /**
     * PENGUJIAN: SubkegiatanController@import (Menguji format_date manual)
     */
    public function test_import_csv_kegiatan_sukses_konversi_tanggal_dd_mm_yyyy()
    {
        $this->authenticateAdmin();

        // PERBAIKAN: Gunakan underscore pada header agar terdeteksi oleh findHeaderIndex di Controller
        $csvContent = implode("\n", [
            "nama_kegiatan,nama_sub_kegiatan,deskripsi,tanggal_mulai,tanggal_selesai,jabatan,tarif,satuan,basis_volume,beban_anggaran",
            "Sensus Penduduk 2026,Pencacahan Lapangan,Deskripsi Testing,15/08/2026,20/08/2026,,,,,"
        ]);

        $file = UploadedFile::fake()->createWithContent('data_kegiatan.csv', $csvContent);

        $response = $this->postJson('/api/subkegiatan/import', [
            'file' => $file
        ]);

        // Fitur Debugging: Jika sukses bukan 1, tampilkan error aslinya di terminal
        if ($response->json('successCount') !== 1) {
            dd($response->json()); 
        }

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('successCount', 1);

        // Ekspektasi 1: Kegiatan Induk berhasil terbuat dari CSV
        $this->assertDatabaseHas('kegiatan', [
            'nama_kegiatan' => 'Sensus Penduduk 2026'
        ]);

        // Ekspektasi 2: Fungsi formatDate() Anda berhasil mengubah '15/08/2026' menjadi '2026-08-15'
        $this->assertDatabaseHas('subkegiatan', [
            'nama_sub_kegiatan' => 'Pencacahan Lapangan',
            'tanggal_mulai' => '2026-08-15 00:00:00',
            'tanggal_selesai' => '2026-08-20 00:00:00'
        ]);
    }
}