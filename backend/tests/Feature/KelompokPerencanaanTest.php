<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;
use App\Models\JabatanMitra;
use App\Models\Perencanaan;
use App\Models\KelompokPerencanaan;

class KelompokPerencanaanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Bypass Trigger SQLite untuk ID Subkegiatan agar tidak error constraint
        Subkegiatan::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'sub_' . mt_rand(1000, 9999);
            }
        });
    }

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_kelompok',
            'email' => 'admin_kp@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    /**
     * TEST 1: Tambah Anggota ke Perencanaan
     */
    public function test_tambah_anggota_ke_perencanaan_berhasil()
    {
        $admin = $this->authenticateAdmin();

        // Setup Data
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Test Kegiatan']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Test Sub']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        
        $mitra = Mitra::create(['nama_lengkap' => 'Siti', 'nik' => '888', 'nomor_hp' => '085']);
        
        // Buat jabatan agar valid
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);

        $payload = [
            'id_perencanaan' => $perencanaan->id,
            'id_mitra'       => $mitra->id,
            'kode_jabatan'   => 'PCL',
            'volume_tugas'   => 15
        ];

        $response = $this->postJson('/api/kelompok-perencanaan', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('kelompok_perencanaan', [
            'id_perencanaan' => $perencanaan->id,
            'id_mitra'       => $mitra->id,
            'volume_tugas'   => 15
        ]);
    }

    /**
     * TEST 2: Gagal Tambah Anggota yang Sama (Duplikasi)
     */
    public function test_gagal_tambah_mitra_jika_sudah_ada_di_tim()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Test Kegiatan']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Test Sub']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        
        $mitra = Mitra::create(['nama_lengkap' => 'Udin', 'nik' => '777', 'nomor_hp' => '089']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);

        // Masukkan mitra secara manual ke DB
        KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 1
        ]);

        // Coba masukkan lagi via API
        $response = $this->postJson('/api/kelompok-perencanaan', [
            'id_perencanaan' => $perencanaan->id,
            'id_mitra'       => $mitra->id,
            'kode_jabatan'   => 'PCL',
            'volume_tugas'   => 5
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Mitra ini sudah ada dalam tim perencanaan tersebut.');
    }

    /**
     * TEST 3: Update Volume Tugas Anggota
     */
    public function test_update_anggota_berhasil()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Test Kegiatan']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Test Sub']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        
        $mitra = Mitra::create(['nama_lengkap' => 'Joko', 'nik' => '555', 'nomor_hp' => '087']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);

        $kp = KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 5
        ]);

        $response = $this->putJson("/api/kelompok-perencanaan/{$kp->id}", [
            'volume_tugas' => 20
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('kelompok_perencanaan', [
            'id' => $kp->id,
            'volume_tugas' => 20
        ]);
    }

    /**
     * TEST 4: Hapus Anggota (PERBAIKAN UTAMA DI SINI)
     */
    public function test_hapus_anggota_berhasil()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Test Kegiatan']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Test Sub']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        $mitra = Mitra::create(['nama_lengkap' => 'Dewi', 'nik' => '444', 'nomor_hp' => '081']);
        
        // PERBAIKAN: Buat JabatanMitra terlebih dahulu!
        // Tanpa ini, insert ke kelompok_perencanaan akan gagal FK Constraint karena kode_jabatan 'PCL' belum ada di tabel induk
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah Lapangan']);

        $kp = KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL', 
            'volume_tugas' => 1
        ]);

        $response = $this->deleteJson("/api/kelompok-perencanaan/{$kp->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('kelompok_perencanaan', ['id' => $kp->id]);
    }
}