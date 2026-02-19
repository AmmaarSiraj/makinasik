<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
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
        
        // 1. Bypass Trigger SQLite untuk ID Subkegiatan (agar tidak error constraint)
        Subkegiatan::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'sub_' . mt_rand(1000, 9999);
            }
        });

        // 2. SOLUSI PENTING: Daftarkan fungsi 'MONTH' ke SQLite
        // Ini membuat SQLite di lingkungan testing "mengerti" perintah MONTH() dari MySQL
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

    /**
     * TEST 1: Buat Perencanaan Baru (Header + Anggota)
     */
    public function test_store_perencanaan_beserta_anggota_berhasil()
    {
        $admin = $this->authenticateAdmin();

        // Data Master
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus Ekonomi']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Listing']);
        $mitra = Mitra::create(['nama_lengkap' => 'Ahmad', 'nik' => '12345', 'nomor_hp' => '0812']);
        
        // Pastikan jabatan ada
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);

        $payload = [
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id,
            'anggota'        => [
                [
                    'id_mitra'     => $mitra->id,
                    'kode_jabatan' => 'PCL',
                    'volume_tugas' => 5
                ]
            ]
        ];

        $response = $this->postJson('/api/perencanaan', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        // Cek Header Perencanaan
        $this->assertDatabaseHas('perencanaan', [
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id
        ]);

        // Cek Anggota Masuk
        $perencanaanId = $response->json('data.id_perencanaan');
        $this->assertDatabaseHas('kelompok_perencanaan', [
            'id_perencanaan' => $perencanaanId,
            'id_mitra'       => $mitra->id,
            'volume_tugas'   => 5
        ]);
    }

    /**
     * TEST 2: Validasi Duplikasi Subkegiatan
     * Satu subkegiatan hanya boleh punya satu perencanaan.
     */
    public function test_gagal_buat_perencanaan_jika_sudah_ada()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus Pertanian']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Listing']);

        // Buat pertama kali (Manual)
        Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);

        // Coba buat lagi lewat API untuk subkegiatan yang sama
        $payload = [
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id
        ];

        $response = $this->postJson('/api/perencanaan', $payload);

        $response->assertStatus(409) // Conflict
                 ->assertJsonPath('message', 'Perencanaan untuk subkegiatan ini sudah ada');
    }

    /**
     * TEST 3: Get By Mitra & Periode
     */
    public function test_get_perencanaan_by_mitra_and_periode()
    {
        $admin = $this->authenticateAdmin();
        
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Survei Bulanan']);
        $sub = Subkegiatan::create([
            'id_kegiatan' => $kegiatan->id, 
            'nama_sub_kegiatan' => 'Pencacahan',
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-31'
        ]);
        
        $mitra = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '999', 'nomor_hp' => '089']);
        JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas']);
        
        // Setup Honorarium agar kalkulasi nominal tidak nol
        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Dokumen']);
        Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan' => 'PML',
            'tarif' => 10000,
            'id_satuan' => $satuan->id,
            'basis_volume' => 10
        ]);

        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => $admin->id]);
        
        KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PML',
            'volume_tugas' => 10
        ]);

        // Call API
        $response = $this->getJson("/api/perencanaan/mitra/{$mitra->id}/periode/2026-03");

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $this->assertCount(1, $response->json('data'));
        // Verifikasi kalkulasi: 10 * 10.000 = 100.000
        $this->assertEquals(100000, $response->json('data.0.total_honor_estimasi'));
    }

    /**
     * TEST 4: Preview Import (Validasi File)
     */
    public function test_preview_import_validasi_file()
    {
        $this->authenticateAdmin();
        Excel::fake();

        // 1. Kirim tanpa file
        $response = $this->postJson('/api/perencanaan/preview-import', []);
        $response->assertStatus(422); // Validation error required

        // 2. Kirim file kosong / fake Excel
        $file = UploadedFile::fake()->create('perencanaan.xlsx', 100);
        $response = $this->postJson('/api/perencanaan/preview-import', ['file' => $file]);
        
        // Harusnya 400 karena isi Excel kosong (array_shift headers akan gagal/kosong)
        $response->assertStatus(400); 
    }

    /**
     * TEST 5: Rekap Bulanan (Menguji Fungsi MONTH di SQLite)
     */
    public function test_get_rekap_bulanan_berjalan()
    {
        $this->authenticateAdmin();
        
        // Setup data dummy agar query rekap tidak kosong
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create([
            'id_kegiatan' => $kegiatan->id, 
            'nama_sub_kegiatan' => 'Pencacahan', 
            'tanggal_mulai' => '2026-02-01' // Bulan Februari
        ]);
        
        $perencanaan = Perencanaan::create(['id_subkegiatan' => $sub->id, 'id_pengawas' => 1]);
        $mitra = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '123', 'nomor_hp' => '081']);
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);
        
        KelompokPerencanaan::create([
            'id_perencanaan' => $perencanaan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => 'PCL',
            'volume_tugas' => 10
        ]);

        // Panggil endpoint yang menggunakan DB::raw('MONTH(...)')
        $response = $this->getJson('/api/rekap/bulanan?year=2026');
        
        // Assert sukses (200), bukan 500 error SQL
        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'applied_limit', 'data']);
                 
        // Opsional: Cek apakah data bulan Februari masuk
        $data = $response->json('data');
        $this->assertTrue(collect($data)->contains('bulan_angka', 2), 'Data bulan Februari harus ada.');
    }
}