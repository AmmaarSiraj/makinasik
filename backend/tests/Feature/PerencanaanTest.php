<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;
use App\Models\JabatanMitra;
use App\Models\Perencanaan;
use App\Models\KelompokPerencanaan;
use App\Models\Honorarium;
use App\Models\SatuanKegiatan;

class PerencanaanTest extends TestCase
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

        // Mendaftarkan fungsi MONTH untuk SQLite agar tidak error di getRekapBulanan dll
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction('MONTH', function ($value) {
                return (int) date('m', strtotime($value));
            });
        }
    }

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_perencanaan',
            'email' => 'admin_plan@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    /* ==========================================
     * PENGUJIAN BASIC CRUD (INDEX, SHOW, DESTROY)
     * ========================================== */

    public function test_index_perencanaan_berhasil()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus A']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub A']);
        Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);

        $response = $this->getJson('/api/perencanaan');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_show_perencanaan_berhasil_dan_not_found()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus B']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub B']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);

        // Tes Sukses
        $resSukses = $this->getJson('/api/perencanaan/' . $perencanaan->id);
        $resSukses->assertStatus(200)->assertJsonPath('status', 'success');

        // Tes Gagal 404
        $resGagal = $this->getJson('/api/perencanaan/9999');
        $resGagal->assertStatus(404);
    }

    public function test_hapus_perencanaan_berhasil_dan_not_found()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus C']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub C']);
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);

        // Tes Sukses Hapus
        $resSukses = $this->deleteJson('/api/perencanaan/' . $perencanaan->id);
        $resSukses->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('perencanaan', ['id' => $perencanaan->id]);

        // Tes Gagal Hapus 404
        $resGagal = $this->deleteJson('/api/perencanaan/9999');
        $resGagal->assertStatus(404);
    }


    /* ==========================================
     * PENGUJIAN FUNGSI GET ANGGOTA 
     * ========================================== */
    public function test_get_anggota_perencanaan_berhasil()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Z']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Z']);
        $mitra = Mitra::create(['nama_lengkap' => 'Target Anggota', 'nik' => '998877', 'nomor_hp' => '0812']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);
        
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        
        KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 5
        ]);

        $response = $this->getJson('/api/perencanaan/' . $perencanaan->id . '/anggota');
        $response->assertStatus(200);
        $this->assertEquals('Target Anggota', $response->json('0.nama_lengkap'));
        $this->assertEquals(5, $response->json('0.volume_tugas'));
    }


    /* ==========================================
     * PENGUJIAN FUNGSI STORE (INSERT MANUAL)
     * ========================================== */
    public function test_store_perencanaan_beserta_anggota_berhasil()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus Ekonomi']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Listing']);
        $mitra = Mitra::create(['nama_lengkap' => 'Ahmad', 'nik' => '12345', 'nomor_hp' => '0812']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);

        $payload = [
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id,
            'anggota'        => [
                ['id_mitra' => $mitra->id, 'kode_jabatan' => 'PCL', 'volume_tugas' => 5]
            ]
        ];

        $response = $this->postJson('/api/perencanaan', $payload);
        $response->assertStatus(201)->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('perencanaan', ['id_subkegiatan' => $sub->id]);
        $this->assertDatabaseHas('kelompok_perencanaan', ['id_mitra' => $mitra->id, 'volume_tugas' => 5]);
    }

    public function test_gagal_buat_perencanaan_jika_sudah_ada()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus Pertanian']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Listing']);

        Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);

        $payload = ['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id];
        $response = $this->postJson('/api/perencanaan', $payload);
        $response->assertStatus(409)->assertJsonPath('message', 'Perencanaan untuk subkegiatan ini sudah ada');
    }


    /* ==========================================
     * PENGUJIAN FUNGSI GET BY MITRA & PERIODE
     * ========================================== */
    public function test_get_perencanaan_by_mitra_and_periode()
    {
        $admin = $this->authenticateAdmin();
        
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei Bulanan']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan', 'tanggal_mulai' => '2026-03-01']);
        
        $mitra = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '999', 'nomor_hp' => '089']);
        JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas']);
        
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Dokumen']);
        Honorarium::create(['id_subkegiatan' => $sub->id, 'kode_jabatan' => 'PML', 'tarif' => 10000, 'id_satuan' => $satuan->id, 'basis_volume' => 10]);

        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        KelompokPerencanaan::create(['id_perencanaan' => $perencanaan->id, 'id_mitra' => $mitra->id, 'kode_jabatan' => 'PML', 'volume_tugas' => 10]);

        $response = $this->getJson("/api/perencanaan/mitra/{$mitra->id}/periode/2026-03");
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertEquals(100000, $response->json('data.0.total_honor_estimasi'));

        // Tes Error Format 
        $resError = $this->getJson("/api/perencanaan/mitra/{$mitra->id}/periode/2026");
        $resError->assertStatus(400)->assertJsonPath('message', 'Format periode salah. Gunakan YYYY-MM');
    }


    /* ==========================================
     * PENGUJIAN FUNGSI IMPORT EXCEL (PREVIEW & STORE)
     * ========================================== */
    public function test_preview_import_menolak_file_excel_kosong()
    {
        $this->authenticateAdmin();
        $file = UploadedFile::fake()->create('test.xlsx', 100);
        $response = $this->postJson('/api/perencanaan/preview-import', ['file' => $file]);
        $response->assertStatus(400); 
    }

    public function test_preview_import_berhasil_baca_data_dan_validasi_warnings()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Excel']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Excel', 'tanggal_mulai' => '2026-01-01']);
        Mitra::create(['nama_lengkap' => 'Joko', 'nik' => '123', 'sobat_id' => 'SBT-001', 'nomor_hp' => '08']);
        JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas']);

        // CSV Simulation
        $csvContent = implode("\n", [
            "Nama Kegiatan,Nama Sub Kegiatan,Sobat ID,Jabatan,Volume",
            "Kegiatan Excel,Sub Excel,SBT-001,Pengawas,5", // Baris Benar
            "Kegiatan Palsu,Sub Palsu,SBT-002,Ngawur,2"  // Baris Warning
        ]);

        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);
        $response = $this->postJson('/api/perencanaan/preview-import', ['file' => $file]);
        $response->assertStatus(200);

        // Pastikan Data yang benar ditangkap
        $this->assertCount(1, $response->json('valid_data'));
        $this->assertEquals('SBT-001', $response->json('valid_data.0.sobat_id'));
        $this->assertNotEmpty($response->json('warnings'));
    }

    public function test_store_import_berhasil()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub']);
        $mitra = Mitra::create(['nama_lengkap' => 'Target', 'nik' => '999', 'nomor_hp' => '080']);
        JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas']);

        $payload = [
            'data' => [
                ['id_subkegiatan' => $sub->id, 'id_mitra' => $mitra->id, 'kode_jabatan' => 'PML', 'volume' => 3]
            ]
        ];

        $response = $this->postJson('/api/perencanaan/store-import', $payload);
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('kelompok_perencanaan', ['id_mitra' => $mitra->id, 'volume_tugas' => 3]);
    }


    /* ==========================================
     * PENGUJIAN FUNGSI REKAP (BULANAN, MITRA, DETAIL)
     * ========================================== */
    public function test_get_rekap_bulanan_berjalan()
    {
        $this->authenticateAdmin();
        
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan', 'tanggal_mulai' => '2026-02-01']);
        
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => 1]);
        $mitra = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '123', 'nomor_hp' => '081']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);
        
        KelompokPerencanaan::create(['id_perencanaan' => $perencanaan->id, 'id_mitra' => $mitra->id, 'kode_jabatan' => 'PCL', 'volume_tugas' => 10]);

        $response = $this->getJson('/api/rekap/bulanan?year=2026');
        $response->assertStatus(200)->assertJsonStructure(['status', 'applied_limit', 'data']);
                 
        $data = $response->json('data');
        $this->assertTrue(collect($data)->contains('bulan_angka', 2));
    }

    public function test_get_rekap_mitra_berjalan()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Rekap Mitra']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Rekap', 'tanggal_mulai' => '2026-02-15']);
        $mitra = Mitra::create(['nama_lengkap' => 'Udin', 'nik' => '555', 'nomor_hp' => '000']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'PCL']);

        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => 1]);
        KelompokPerencanaan::create(['id_perencanaan' => $perencanaan->id, 'id_mitra' => $mitra->id, 'kode_jabatan' => 'PCL', 'volume_tugas' => 1]);

        // Hit fungsi
        $response = $this->getJson('/api/rekap/mitra?year=2026&month=02');
        $response->assertStatus(200);
        $this->assertEquals('Udin', $response->json('data.0.nama_lengkap'));

        // Hit kosong jika tidak ada param
        $resKosong = $this->getJson('/api/rekap/mitra');
        $resKosong->assertStatus(200)->assertJson(['data' => []]);
    }

    public function test_get_rekap_detail_berjalan()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Detail Mitra']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Detail', 'tanggal_mulai' => '2026-02-15']);
        $mitra = Mitra::create(['nama_lengkap' => 'Udin Detail', 'nik' => '555666', 'nomor_hp' => '000']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'PCL']);

        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => 1]);
        KelompokPerencanaan::create(['id_perencanaan' => $perencanaan->id, 'id_mitra' => $mitra->id, 'kode_jabatan' => 'PCL', 'volume_tugas' => 2]);

        $response = $this->getJson('/api/rekap/detail?year=2026&month=02&mitra_id=' . $mitra->id);
        $response->assertStatus(200);
        $this->assertEquals('Sub Detail', $response->json('data.0.nama_sub_kegiatan'));
        $this->assertEquals(2, $response->json('data.0.volume_tugas'));
    }

    public function test_model_perencanaan_relation_berjalan_dengan_benar()
    {
        // 1. Setup Data Master
        $admin = User::create([
            'username' => 'admin_relasi_perencanaan',
            'email' => 'admin_relasi_p@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Relasi']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Relasi']);

        // 2. Buat data Perencanaan utama
        $perencanaan = Perencanaan::create([
            'id_subkegiatan' => $sub->id,
            'id_pengawas' => $admin->id
        ]);

        // 3. Buat data anak (Kelompok Perencanaan)
        $mitra = Mitra::create(['nama_lengkap' => 'Mitra Relasi', 'nik' => '1234567890123456', 'nomor_hp' => '08123']);
        JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas']);

        KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PML',
            'volume_tugas' => 10
        ]);

        // 4. Panggil dan uji relasi subkegiatan()
        $this->assertInstanceOf(Subkegiatan::class, $perencanaan->subkegiatan);
        $this->assertEquals('Sub Relasi', $perencanaan->subkegiatan->nama_sub_kegiatan);

        // 5. Panggil dan uji relasi pengawas()
        $this->assertInstanceOf(User::class, $perencanaan->pengawas);
        $this->assertEquals('admin_relasi_perencanaan', $perencanaan->pengawas->username);

        // 6. PERBAIKAN: Gunakan 'kelompok' sesuai dengan fungsi di model Perencanaan.php
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $perencanaan->kelompok);
        $this->assertCount(1, $perencanaan->kelompok);
        $this->assertEquals('PML', $perencanaan->kelompok->first()->kode_jabatan);
    }
}