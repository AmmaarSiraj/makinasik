<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;
use App\Models\JabatanMitra;
use App\Models\Penugasan;
use App\Models\KelompokPenugasan;
use App\Models\Perencanaan;
use App\Models\KelompokPerencanaan;
use App\Models\AturanPeriode;
use App\Models\TahunAktif;
use App\Models\Honorarium;
use App\Models\SatuanKegiatan;

class PenugasanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Bypass Trigger SQLite untuk ID Subkegiatan agar tidak error 'Constraint Failed'
        Subkegiatan::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'sub_' . mt_rand(1000, 9999);
            }
        });
    }

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_penugasan',
            'email' => 'admin_p@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    /* ==========================================
     * PENGUJIAN BASIC CRUD (INDEX, SHOW, DESTROY, UPDATE NOT FOUND)
     * ========================================== */

    public function test_index_penugasan_berhasil()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan']);
        Penugasan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);

        $response = $this->getJson('/api/penugasan');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_show_penugasan_berhasil_dan_not_found()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub']);
        $penugasan = Penugasan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);

        $response = $this->getJson('/api/penugasan/' . $penugasan->id);
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $res404 = $this->getJson('/api/penugasan/9999');
        $res404->assertStatus(404);
    }

    public function test_hapus_penugasan_berhasil_dan_not_found()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub']);
        $penugasan = Penugasan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);

        $response = $this->deleteJson('/api/penugasan/' . $penugasan->id);
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('penugasan', ['id' => $penugasan->id]);

        $res404 = $this->deleteJson('/api/penugasan/9999');
        $res404->assertStatus(404);
    }

    public function test_update_penugasan_gagal_not_found()
    {
        $this->authenticateAdmin();
        $response = $this->putJson('/api/penugasan/9999', ['status_penugasan' => 'disetujui']);
        $response->assertStatus(404);
    }

    /* ==========================================
     * PENGUJIAN FUNGSI GET ANGGOTA (JOIN QUERY)
     * ========================================== */
    public function test_get_anggota_penugasan_berhasil()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub']);
        $mitra = Mitra::create(['nama_lengkap' => 'Anggota 1', 'nik' => '123', 'nomor_hp' => '08']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);
        
        $penugasan = Penugasan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        KelompokPenugasan::create([
            'id_penugasan' => $penugasan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 5
        ]);

        $response = $this->getJson('/api/penugasan/' . $penugasan->id . '/anggota');
        $response->assertStatus(200);
        $this->assertEquals('Anggota 1', $response->json('0.nama_lengkap'));
        $this->assertEquals(5, $response->json('0.volume_tugas'));
    }

    /* ==========================================
     * PENGUJIAN 1: STORE PENUGASAN (BESERTA ANGGOTA)
     * ========================================== */
    public function test_buat_penugasan_baru_beserta_anggota_berhasil()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan']);
        $mitra1 = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '111', 'nomor_hp' => '081']);
        $jabatanPML = JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas Lapangan']);

        $payload = [
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id,
            'anggota'        => [
                [
                    'id_mitra'     => $mitra1->id,
                    'kode_jabatan' => $jabatanPML->kode_jabatan,
                    'volume_tugas' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/penugasan', $payload);
        $response->assertStatus(201)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('kelompok_penugasan', ['id_mitra' => $mitra1->id]);
    }

    public function test_buat_penugasan_gagal_jika_format_anggota_salah_422()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan']);

        $response = $this->postJson('/api/penugasan', [
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id,
            'anggota'        => [['id_mitra' => 9999, 'kode_jabatan' => 'PCL', 'volume_tugas' => 1]]
        ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['anggota.0.id_mitra']]);
    }

    /* ==========================================
     * PENGUJIAN: GET BY MITRA & PERIODE
     * ========================================== */
    public function test_get_penugasan_by_mitra_and_periode()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create([
            'id_kegiatan' => $kegiatan->id, 
            'nama_sub_kegiatan' => 'Pencacahan',
            'tanggal_mulai' => '2026-08-10'
        ]);
        $mitra = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '111', 'nomor_hp' => '081']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah Lapangan']);

        $penugasan = Penugasan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id, 'status_penugasan' => 'disetujui']);
        KelompokPenugasan::create(['id_penugasan' => $penugasan->id, 'id_mitra' => $mitra->id, 'kode_jabatan' => 'PCL', 'volume_tugas' => 1]);

        $response = $this->getJson('/api/penugasan/mitra/' . $mitra->id . '/periode/2026-08');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertEquals('Sensus 2026', $response->json('data.0.nama_kegiatan'));

        // Test Error Format
        $errResp = $this->getJson('/api/penugasan/mitra/' . $mitra->id . '/periode/2026');
        $errResp->assertStatus(400)->assertJsonPath('message', 'Format periode salah');
    }

    /* ==========================================
     * PENGUJIAN: UPDATE STATUS (FRONTEND TOGGLE)
     * ========================================== */
    public function test_update_status_penugasan_berhasil_di_toggle()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan']);
        $penugasan = Penugasan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id, 'status_penugasan' => 'menunggu']);

        $response1 = $this->putJson('/api/penugasan/' . $penugasan->id, ['status_penugasan' => 'disetujui']);
        $response1->assertStatus(200)->assertJsonPath('data.status_penugasan', 'disetujui'); 
    }

    public function test_import_from_perencanaan_berhasil()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub']);
        $mitra = Mitra::create(['nama_lengkap' => 'Target Import', 'nik' => '999', 'nomor_hp' => '080']);
        
        // PERBAIKAN: Buat Jabatan Mitra Dulu
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);

        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL', // Sekarang 'PCL' sudah ada
            'volume_tugas' => 5
        ]);

        $response = $this->postJson('/api/penugasan/import-perencanaan', [
            'ids_perencanaan' => [$perencanaan->id]
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('penugasan', ['id_subkegiatan' => $sub->id]);
        $this->assertDatabaseHas('kelompok_penugasan', ['id_mitra' => $mitra->id, 'volume_tugas' => 5]);
    }

    /* ==========================================
     * PENGUJIAN: STORE IMPORT (EXCEL SUBMIT)
     * ========================================== */
    public function test_store_import_berhasil()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub']);
        $mitra = Mitra::create(['nama_lengkap' => 'Target Import', 'nik' => '999', 'nomor_hp' => '080']);

        // PERBAIKAN: Buat Jabatan Mitra Dulu
        JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas Lapangan']);

        $payload = [
            'data' => [
                [
                    'id_subkegiatan' => $sub->id,
                    'id_mitra' => $mitra->id,
                    'kode_jabatan' => 'PML', // Sekarang 'PML' sudah ada
                    'volume' => 3
                ]
            ]
        ];

        $response = $this->postJson('/api/penugasan/store-import', $payload);
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('kelompok_penugasan', ['id_mitra' => $mitra->id, 'volume_tugas' => 3]);
    }

    /* ==========================================
     * PENGUJIAN: PREVIEW IMPORT EXCEL
     * ========================================== */
    public function test_preview_import_menolak_file_excel_kosong()
    {
        $this->authenticateAdmin();
        Excel::fake(); 
        $file = UploadedFile::fake()->create('penugasan.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $this->postJson('/api/penugasan/preview-import', ['file' => $file]);
        $response->assertStatus(400)->assertJsonPath('message', 'File kosong atau format salah.');
    }

    public function test_preview_import_berhasil_baca_data_dan_validasi_warnings()
    {
        $this->authenticateAdmin();

        // Siapkan DB Master
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Excel']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Excel', 'tanggal_mulai' => '2026-01-01']);
        $mitra = Mitra::create(['nama_lengkap' => 'Joko', 'nik' => '123', 'sobat_id' => 'SBT-001', 'nomor_hp' => '08']);
        TahunAktif::create(['user_id' => $mitra->id, 'tahun' => '2026', 'status' => 'aktif']);
        JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas']);

        // Membuat CSV file
        $csvContent = implode("\n", [
            "Nama Kegiatan,Nama Sub Kegiatan,Sobat ID,Jabatan,Volume",
            "Kegiatan Excel,Sub Excel,SBT-001,Pengawas,5", // Baris Benar
            "Kegiatan Palsu,Sub Palsu,SBT-002,Ngawur,2"  // Baris Penuh Error (Warnings)
        ]);

        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

        $response = $this->postJson('/api/penugasan/preview-import', ['file' => $file]);
        $response->assertStatus(200);

        // Baris pertama (Valid Data)
        $this->assertCount(1, $response->json('valid_data'));
        $this->assertEquals('SBT-001', $response->json('valid_data.0.sobat_id'));

        // Baris kedua (Akan masuk warnings)
        $this->assertNotEmpty($response->json('warnings'));
    }

    public function test_model_penugasan_relation_berjalan_dengan_benar()
    {
        // 1. Setup Data Master
        $admin = User::create([
            'username' => 'admin_relasi_penugasan',
            'email' => 'admin_relasi_penugasan@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Relasi Penugasan']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Relasi Penugasan']);

        // 2. Buat data Penugasan utama
        $penugasan = Penugasan::create([
            'id_subkegiatan' => $sub->id,
            'id_pengawas' => $admin->id,
            'status_penugasan' => 'menunggu'
        ]);

        // 3. Buat data anak (Kelompok Penugasan)
        $mitra = Mitra::create(['nama_lengkap' => 'Mitra Relasi Penugasan', 'nik' => '1234567890123499', 'nomor_hp' => '0812399']);
        JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas Lapangan']);

        KelompokPenugasan::create([
            'id_penugasan' => $penugasan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PML',
            'volume_tugas' => 10
        ]);

        // 4. Panggil dan uji relasi subkegiatan()
        $this->assertInstanceOf(Subkegiatan::class, $penugasan->subkegiatan);
        $this->assertEquals('Sub Relasi Penugasan', $penugasan->subkegiatan->nama_sub_kegiatan);

        // 5. Panggil dan uji relasi pengawas()
        $this->assertInstanceOf(User::class, $penugasan->pengawas);
        $this->assertEquals('admin_relasi_penugasan', $penugasan->pengawas->username);

        // 6. Panggil dan uji relasi kelompok() atau kelompokPenugasan()
        // CATATAN: Jika error 'null', sesuaikan nama 'kelompokPenugasan' dengan fungsi di Models/Penugasan.php
        // Mengingat model Perencanaan kamu menggunakan nama 'kelompok', coba pakai 'kelompok' jika ini error.
        $namaRelasi = $penugasan->kelompokPenugasan ?? $penugasan->kelompok; 

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $namaRelasi);
        $this->assertCount(1, $namaRelasi);
        $this->assertEquals('PML', $namaRelasi->first()->kode_jabatan);
    }
}