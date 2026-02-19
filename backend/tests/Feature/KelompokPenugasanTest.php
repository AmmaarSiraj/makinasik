<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;
use App\Models\JabatanMitra;
use App\Models\Penugasan;
use App\Models\KelompokPenugasan;

class KelompokPenugasanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Bypass Trigger SQLite untuk ID Subkegiatan
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
            'email' => 'admink@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    private function siapkanDataInduk($admin)
    {
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah Lapangan']);
        
        $penugasan = Penugasan::create([
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id,
            'status_penugasan' => 'menunggu'
        ]);

        return $penugasan;
    }

    /**
     * PENGUJIAN 1: STORE ANGGOTA & CEK DUPLIKASI (400)
     */
    public function test_tambah_anggota_berhasil_dan_gagal_jika_mitra_sudah_ada_di_tim()
    {
        $admin = $this->authenticateAdmin();
        $penugasan = $this->siapkanDataInduk($admin);
        $mitra = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '111', 'nomor_hp' => '081']);

        // 1. Tambah Mitra Budi ke Penugasan (SUKSES)
        $response1 = $this->postJson('/api/kelompok-penugasan', [
            'id_penugasan' => $penugasan->id,
            'id_mitra'     => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 1
        ]);

        $response1->assertStatus(201)
                  ->assertJsonPath('status', 'success');

        // 2. Coba tambahkan Budi LAGI ke Penugasan yang sama (GAGAL 400)
        $response2 = $this->postJson('/api/kelompok-penugasan', [
            'id_penugasan' => $penugasan->id,
            'id_mitra'     => $mitra->id, // Duplikat di penugasan yang sama
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 2
        ]);

        // Membuktikan validasi $exists di controller bekerja
        $response2->assertStatus(400)
                  ->assertJsonPath('message', 'Mitra ini sudah ada dalam tim penugasan tersebut.');
    }

    /**
     * PENGUJIAN 2: CEK FORMAT MAP (INDEX)
     */
    public function test_index_mengembalikan_format_data_yang_dimanipulasi_dengan_benar()
    {
        $admin = $this->authenticateAdmin();
        $penugasan = $this->siapkanDataInduk($admin);
        $mitra = Mitra::create(['nama_lengkap' => 'Siti', 'nik' => '222', 'nomor_hp' => '082']);

        KelompokPenugasan::create([
            'id_penugasan' => $penugasan->id,
            'id_mitra'     => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 5
        ]);

        $response = $this->getJson('/api/kelompok-penugasan');

        $response->assertStatus(200);
        
        // Membuktikan fungsi $data->map() di index() berhasil mengambil relasi
        $data = $response->json('data.0');
        $this->assertEquals('Siti', $data['nama_mitra']);
        $this->assertEquals('Pencacah Lapangan', $data['nama_jabatan']);
        $this->assertEquals('admin_kelompok', $data['nama_pengawas']);
        $this->assertEquals(5, $data['volume_tugas']);
    }

    /**
     * PENGUJIAN 3: UPDATE & DELETE ANGGOTA
     */
    public function test_update_volume_dan_hapus_anggota_berhasil()
    {
        $admin = $this->authenticateAdmin();
        $penugasan = $this->siapkanDataInduk($admin);
        $mitra = Mitra::create(['nama_lengkap' => 'Joko', 'nik' => '333', 'nomor_hp' => '083']);

        $anggota = KelompokPenugasan::create([
            'id_penugasan' => $penugasan->id,
            'id_mitra'     => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 2
        ]);

        // 1. UPDATE Volume dari 2 jadi 10
        $responseUpdate = $this->putJson('/api/kelompok-penugasan/' . $anggota->id, [
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 10
        ]);

        $responseUpdate->assertStatus(200);
        $this->assertDatabaseHas('kelompok_penugasan', [
            'id' => $anggota->id,
            'volume_tugas' => 10
        ]);

        // 2. DELETE Anggota dari Penugasan
        $responseDelete = $this->deleteJson('/api/kelompok-penugasan/' . $anggota->id);

        $responseDelete->assertStatus(200)
                       ->assertJsonPath('message', 'Mitra berhasil dihapus dari penugasan.');
                       
        $this->assertDatabaseMissing('kelompok_penugasan', ['id' => $anggota->id]);
    }
}