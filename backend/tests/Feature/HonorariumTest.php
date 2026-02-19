<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;
use App\Models\JabatanMitra;
use App\Models\SatuanKegiatan;
use App\Models\Honorarium;

class HonorariumTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SETUP: Mencegah Error Trigger SQLite pada tabel Subkegiatan
     */
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
            'username' => 'admin_honor',
            'email' => 'adminhonor@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
    }

    /**
     * PENGUJIAN: store() -> Uji Jebakan Validasi Angka dan Relasi
     */
    public function test_tambah_honorarium_ditolak_karena_validasi_ketat()
    {
        $this->authenticateAdmin();

        // Skenario: 
        // 1. Tarif minus (-50000)
        // 2. Basis volume 0 (padahal minimal 1)
        // 3. ID Subkegiatan & Kode Jabatan palsu (tidak ada di database)
        $response = $this->postJson('/api/honorarium', [
            'id_subkegiatan' => 'sub_palsu_999',
            'kode_jabatan'   => 'JAB_PALSU',
            'tarif'          => -50000, 
            'id_satuan'      => 1,
            'basis_volume'   => 0 
        ]);

        // Ekspektasi: Sistem menolak dengan 422 dan mendeteksi ke-4 error tersebut
        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => [
                         'id_subkegiatan', 
                         'kode_jabatan', 
                         'tarif', 
                         'basis_volume'
                     ]
                 ]);
    }

    /**
     * PENGUJIAN: store() -> Sukses (Jalur Bahagia)
     */
    public function test_tambah_honorarium_berhasil_masuk_database()
    {
        $this->authenticateAdmin();

        // 1. Siapkan Data Induk (Relasi)
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei A']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub A']);
        $jabatan = JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah Lapangan']);
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Orang Hari', 'alias' => 'OH']);

        // 2. Tembak API
        $response = $this->postJson('/api/honorarium', [
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 150000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1,
            'beban_anggaran' => 'BPS-01'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        // 3. Cek Database
        $this->assertDatabaseHas('honorarium', [
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => 'PCL',
            'tarif'          => 150000
        ]);
    }

    /**
     * PENGUJIAN: index() -> Manipulasi String (Flattening)
     */
    public function test_format_string_dan_flattening_berhasil_di_get_all()
    {
        $this->authenticateAdmin();

        // 1. Siapkan Data
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan']);
        $jabatan = JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas Lapangan']);
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Orang Bulan', 'alias' => 'OB']);
        
        Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 3000000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1
        ]);

        // 2. Eksekusi Index GET
        $response = $this->getJson('/api/honorarium');

        $response->assertStatus(200);

        // 3. Uji Kritis Manipulasi Data oleh Controller
        $data = $response->json('data.0');

        // Pastikan format "Nama (Kode)" bekerja!
        $this->assertEquals('Pengawas Lapangan (PML)', $data['nama_jabatan']);
        
        // Pastikan format "Satuan (Alias)" bekerja!
        $this->assertEquals('Orang Bulan (OB)', $data['nama_satuan']);

        // Pastikan objek aslinya sudah terhapus (unset) agar payload ringan
        $this->assertArrayNotHasKey('jabatan', $data);
        $this->assertArrayNotHasKey('satuan', $data);
        $this->assertArrayNotHasKey('subkegiatan', $data);
        
        // Pastikan Flattening (naik level) bekerja
        $this->assertEquals('Pencacahan', $data['nama_sub_kegiatan']);
        $this->assertEquals('Sensus 2026', $data['nama_kegiatan']);
    }

    /**
     * PENGUJIAN: destroy()
     */
    public function test_hapus_honorarium_berhasil()
    {
        $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei Cepat']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Entry Data']);
        $jabatan = JabatanMitra::create(['kode_jabatan' => 'OPR', 'nama_jabatan' => 'Operator']);
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Dokumen', 'alias' => 'DOK']);
        
        $honor = Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 5000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1
        ]);

        $response = $this->deleteJson('/api/honorarium/' . $honor->id);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Honorarium berhasil dihapus');

        $this->assertDatabaseMissing('honorarium', ['id' => $honor->id]);
    }
}