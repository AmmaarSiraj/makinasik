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

    /* ==========================================
       1. PENGUJIAN: FUNGSI INDEX
    ========================================== */
    public function test_format_string_dan_flattening_berhasil_di_get_all()
    {
        $this->authenticateAdmin();

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

        $response = $this->getJson('/api/honorarium');

        $response->assertStatus(200);

        $data = $response->json('data.0');

        $this->assertEquals('Pengawas Lapangan (PML)', $data['nama_jabatan']);
        $this->assertEquals('Orang Bulan (OB)', $data['nama_satuan']);
        $this->assertArrayNotHasKey('jabatan', $data);
        $this->assertEquals('Pencacahan', $data['nama_sub_kegiatan']);
    }

    /* ==========================================
       2. PENGUJIAN: FUNGSI STORE
    ========================================== */
    public function test_tambah_honorarium_ditolak_karena_validasi_ketat()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/honorarium', [
            'id_subkegiatan' => 'sub_palsu_999',
            'kode_jabatan'   => 'JAB_PALSU',
            'tarif'          => -50000, 
            'id_satuan'      => 1,
            'basis_volume'   => 0 
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['id_subkegiatan', 'kode_jabatan', 'tarif', 'basis_volume']]);
    }

    public function test_tambah_honorarium_berhasil_masuk_database()
    {
        $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei A']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub A']);
        $jabatan = JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah Lapangan']);
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Orang Hari', 'alias' => 'OH']);

        $response = $this->postJson('/api/honorarium', [
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 150000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1,
            'beban_anggaran' => 'BPS-01'
        ]);

        $response->assertStatus(201)->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('honorarium', [
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => 'PCL',
            'tarif'          => 150000
        ]);
    }

    public function test_tambah_honorarium_ditolak_karena_kombinasi_duplikat()
    {
        $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei Dup']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Dup']);
        $jabatan = JabatanMitra::create(['kode_jabatan' => 'DUP', 'nama_jabatan' => 'Jabatan Dup']);
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'OH', 'alias' => 'OH']);

        // Insert data pertama
        Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 100000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1
        ]);

        // Coba insert data dengan subkegiatan & jabatan yang sama persis
        $response = $this->postJson('/api/honorarium', [
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 150000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('error', 'Jabatan ini sudah terdaftar di kegiatan ini. Sila edit baris yang sudah ada.');
    }

    /* ==========================================
       3. PENGUJIAN: FUNGSI SHOW
    ========================================== */
    public function test_lihat_detail_honorarium_sukses_dan_gagal()
    {
        $this->authenticateAdmin();
        
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei Cek']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Cek']);
        $jabatan = JabatanMitra::create(['kode_jabatan' => 'CEK', 'nama_jabatan' => 'Jabatan Cek']);
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'OH', 'alias' => 'OH']);

        $honor = Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 100000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1
        ]);

        // Uji Sukses
        $responseSukses = $this->getJson('/api/honorarium/' . $honor->id);
        $responseSukses->assertStatus(200)->assertJsonPath('status', 'success');

        // Uji Gagal (ID tidak ditemukan)
        $responseGagal = $this->getJson('/api/honorarium/99999');
        $responseGagal->assertStatus(404)->assertJsonPath('message', 'Data Honorarium tidak ditemukan');
    }

    /* ==========================================
       4. PENGUJIAN: FUNGSI UPDATE
    ========================================== */
    public function test_update_honorarium_berhasil_gagal_dan_not_found()
    {
        $this->authenticateAdmin();
        
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei Upd']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Upd']);
        $jabatan = JabatanMitra::create(['kode_jabatan' => 'UPD', 'nama_jabatan' => 'Jabatan Upd']);
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'OH', 'alias' => 'OH']);

        $honor = Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 100000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1
        ]);

        // 1. Uji Not Found
        $responseNotFound = $this->putJson('/api/honorarium/99999', ['tarif' => 200000]);
        $responseNotFound->assertStatus(404);

        // 2. Uji Gagal Validasi (Tarif Minus)
        $responseValidasi = $this->putJson('/api/honorarium/' . $honor->id, ['tarif' => -100]);
        $responseValidasi->assertStatus(422);

        // 3. Uji Sukses Update
        $responseSukses = $this->putJson('/api/honorarium/' . $honor->id, ['tarif' => 250000]);
        $responseSukses->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('honorarium', ['id' => $honor->id, 'tarif' => 250000]);
    }

    /* ==========================================
       5. PENGUJIAN: FUNGSI DESTROY
    ========================================== */
    public function test_hapus_honorarium_berhasil_dan_gagal()
    {
        $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei Hapus']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Hapus']);
        $jabatan = JabatanMitra::create(['kode_jabatan' => 'DEL', 'nama_jabatan' => 'Operator']);
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Dokumen', 'alias' => 'DOK']);
        
        $honor = Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 5000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1
        ]);

        // Uji sukses hapus
        $responseSukses = $this->deleteJson('/api/honorarium/' . $honor->id);
        $responseSukses->assertStatus(200)->assertJsonPath('message', 'Honorarium berhasil dihapus');
        $this->assertDatabaseMissing('honorarium', ['id' => $honor->id]);

        // Uji gagal karena data tidak ditemukan (sudah terhapus)
        $responseGagal = $this->deleteJson('/api/honorarium/' . $honor->id);
        $responseGagal->assertStatus(404);
    }

    /* ==========================================
       6. PENGUJIAN: FUNGSI GET BY SUBKEGIATAN
    ========================================== */
    public function test_get_by_subkegiatan_mengembalikan_mapping_yang_benar()
    {
        $this->authenticateAdmin();
        
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei Get']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Get']);
        $jabatan = JabatanMitra::create(['kode_jabatan' => 'GET', 'nama_jabatan' => 'Jabatan Get']);
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Orang Hari', 'alias' => 'OH']);

        Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan'   => $jabatan->kode_jabatan,
            'tarif'          => 123000,
            'id_satuan'      => $satuan->id,
            'basis_volume'   => 1
        ]);

        $response = $this->getJson('/api/subkegiatan/' . $sub->id . '/honorarium');
        
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        
        // Memastikan Mapping fungsi GetBySubkegiatan bekerja
        $data = $response->json('data.0');
        $this->assertEquals('Jabatan Get', $data['nama_jabatan']);
        $this->assertEquals('Orang Hari', $data['nama_satuan']);
        $this->assertEquals('OH', $data['satuan_alias']);
    }
}