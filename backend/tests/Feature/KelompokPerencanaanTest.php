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

    /* =========================================================
       1. TEST FUNGSI INDEX
    ========================================================= */
    public function test_index_mengembalikan_format_data_yang_benar()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Z']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Z']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        
        $mitra = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '12345', 'nomor_hp' => '080']);
        JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas']);

        KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PML',
            'volume_tugas' => 10
        ]);

        $response = $this->getJson('/api/kelompok-perencanaan');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // Pastikan Flattening Data Bekerja
        $data = $response->json('data.0');
        $this->assertEquals('Budi', $data['nama_mitra']);
        $this->assertEquals('12345', $data['nik_mitra']);
        $this->assertEquals('Pengawas', $data['nama_jabatan']);
        $this->assertEquals('Sub Z', $data['nama_sub_kegiatan']);
        $this->assertEquals('admin_kelompok', $data['nama_pengawas']);
    }

    /* =========================================================
       2. TEST FUNGSI STORE
    ========================================================= */
    public function test_tambah_anggota_ke_perencanaan_berhasil()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Test Kegiatan']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Test Sub']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        $mitra = Mitra::create(['nama_lengkap' => 'Siti', 'nik' => '888', 'nomor_hp' => '085']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);

        $payload = [
            'id_perencanaan' => $perencanaan->id,
            'id_mitra'       => $mitra->id,
            'kode_jabatan'   => 'PCL',
            'volume_tugas'   => 15
        ];

        $response = $this->postJson('/api/kelompok-perencanaan', $payload);

        $response->assertStatus(201)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('kelompok_perencanaan', ['volume_tugas' => 15]);
    }

    public function test_gagal_tambah_mitra_jika_sudah_ada_di_tim()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Test Kegiatan']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Test Sub']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        $mitra = Mitra::create(['nama_lengkap' => 'Udin', 'nik' => '777', 'nomor_hp' => '089']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);

        KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 1
        ]);

        $response = $this->postJson('/api/kelompok-perencanaan', [
            'id_perencanaan' => $perencanaan->id,
            'id_mitra'       => $mitra->id,
            'kode_jabatan'   => 'PCL',
            'volume_tugas'   => 5
        ]);

        $response->assertStatus(400)->assertJsonPath('message', 'Mitra ini sudah ada dalam tim perencanaan tersebut.');
    }

    public function test_gagal_tambah_mitra_karena_validasi()
    {
        $this->authenticateAdmin();

        // Kosong akan gagal
        $response = $this->postJson('/api/kelompok-perencanaan', []);
        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['id_perencanaan', 'id_mitra', 'kode_jabatan', 'volume_tugas']]);
    }


    /* =========================================================
       3. TEST FUNGSI UPDATE
    ========================================================= */
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

        $response = $this->putJson("/api/kelompok-perencanaan/{$kp->id}", ['volume_tugas' => 20]);
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('kelompok_perencanaan', ['id' => $kp->id, 'volume_tugas' => 20]);
    }

    public function test_update_anggota_gagal_not_found_dan_validasi()
    {
        $this->authenticateAdmin();

        // 404 Not Found
        $resNotFound = $this->putJson('/api/kelompok-perencanaan/9999', ['volume_tugas' => 10]);
        $resNotFound->assertStatus(404);

        // Setup Data asli untuk test validasi
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Tes']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Tes']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => 1]);
        $mitra = Mitra::create(['nama_lengkap' => 'Tes', 'nik' => '111', 'nomor_hp' => '111']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'PCL']);

        $kp = KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 5
        ]);

        // 422 Validasi Gagal (volume minus)
        $resVal = $this->putJson("/api/kelompok-perencanaan/{$kp->id}", ['volume_tugas' => -5]);
        $resVal->assertStatus(422);
    }


    /* =========================================================
       4. TEST FUNGSI DESTROY
    ========================================================= */
    public function test_hapus_anggota_berhasil_dan_gagal()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Test Kegiatan']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Test Sub']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        $mitra = Mitra::create(['nama_lengkap' => 'Dewi', 'nik' => '444', 'nomor_hp' => '081']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah Lapangan']);

        $kp = KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL', 
            'volume_tugas' => 1
        ]);

        // Hapus Sukses
        $response = $this->deleteJson("/api/kelompok-perencanaan/{$kp->id}");
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('kelompok_perencanaan', ['id' => $kp->id]);

        // Hapus Gagal (404)
        $resFail = $this->deleteJson("/api/kelompok-perencanaan/9999");
        $resFail->assertStatus(404);
    }
}