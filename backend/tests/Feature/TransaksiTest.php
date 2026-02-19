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
use App\Models\Honorarium;
use App\Models\SatuanKegiatan;
use App\Models\AturanPeriode;
use Illuminate\Support\Facades\DB;

class TransaksiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Bypass Trigger SQLite untuk ID Subkegiatan (agar tidak error constraint di testing environment)
        Subkegiatan::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'sub_' . mt_rand(1000, 9999);
            }
        });
    }

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_transaksi',
            'email' => 'admin_trx@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    /**
     * Helper untuk membuat skenario data lengkap
     */
    private function createScenario($statusPenugasan = 'disetujui', $bulan = '01', $tarif = 100000, $volume = 1)
    {
        $tahun = '2026';
        $tanggal = "{$tahun}-{$bulan}-10";

        // 1. Data Master
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Test ' . mt_rand(1, 100)]);
        $sub = Subkegiatan::create([
            'id_kegiatan' => $kegiatan->id, 
            'nama_sub_kegiatan' => 'Sub Test',
            'tanggal_mulai' => $tanggal
        ]);
        
        $mitra = Mitra::create([
            'nama_lengkap' => 'Mitra ' . mt_rand(1, 100), 
            'nik' => (string)mt_rand(1000,9999), 
            'sobat_id' => 'SBT'.mt_rand(100,999)
        ]);
        
        $jabatan = JabatanMitra::firstOrCreate(
            ['kode_jabatan' => 'PCL'], 
            ['nama_jabatan' => 'Pencacah']
        );

        $satuan = SatuanKegiatan::firstOrCreate(['nama_satuan' => 'Dokumen']);

        // 2. Honorarium
        Honorarium::create([
            'id_subkegiatan' => $sub->id,
            'kode_jabatan' => $jabatan->kode_jabatan,
            'tarif' => $tarif,
            'id_satuan' => $satuan->id,
            'basis_volume' => 10
        ]);

        // 3. Penugasan Header
        $penugasan = Penugasan::create([
            'id_subkegiatan' => $sub->id,
            'id_pengawas' => 1,
            'status_penugasan' => $statusPenugasan // Kunci pengujian ada di sini
        ]);

        // 4. Detail Anggota
        KelompokPenugasan::create([
            'id_penugasan' => $penugasan->id,
            'id_mitra' => $mitra->id,
            'kode_jabatan' => $jabatan->kode_jabatan,
            'volume_tugas' => $volume
        ]);

        return compact('kegiatan', 'sub', 'mitra', 'penugasan');
    }

    /**
     * TEST 1: Validasi Parameter Tahun
     * Endpoint harus menolak jika tidak ada query ?tahun=...
     */
    public function test_transaksi_index_wajib_tahun()
    {
        $this->authenticateAdmin();

        $response = $this->getJson('/api/transaksi');

        $response->assertStatus(400)
                 ->assertJsonPath('error', 'Filter Tahun wajib diisi.');
    }

    /**
     * TEST 2: Kalkulasi Honor (Hanya Status Approved)
     * Skenario: 
     * - Mitra A punya tugas Approved (Tarif 100rb x 2 = 200rb)
     * - Mitra A punya tugas Menunggu (Tarif 50rb x 10 = 500rb)
     * Ekspektasi: Total Pendapatan Mitra A adalah 200rb (yang menunggu diabaikan)
     */
    public function test_transaksi_hanya_menghitung_status_approved()
    {
        $this->authenticateAdmin();
        
        // Set Aturan Batas Honor (agar status_aman terhitung benar)
        AturanPeriode::create(['periode' => '2026', 'batas_honor' => 4000000]);

        // 1. Buat Transaksi Approved
        $data1 = $this->createScenario('disetujui', '03', 100000, 2); // 200.000
        $mitraId = $data1['mitra']->id;

        // 2. Buat Transaksi Menunggu (Mitra yang sama)
        // Manual insert agar nempel ke mitra yang sama
        $subPending = Subkegiatan::create(['id_kegiatan' => $data1['kegiatan']->id, 'nama_sub_kegiatan' => 'Sub Pending', 'tanggal_mulai' => '2026-03-15']);
        Honorarium::create(['id_subkegiatan' => $subPending->id, 'kode_jabatan' => 'PCL', 'tarif' => 50000, 'id_satuan' => 1]);
        $pPending = Penugasan::create(['id_subkegiatan' => $subPending->id, 'id_pengawas' => 1, 'status_penugasan' => 'menunggu']);
        KelompokPenugasan::create(['id_penugasan' => $pPending->id, 'id_mitra' => $mitraId, 'kode_jabatan' => 'PCL', 'volume_tugas' => 10]); // 500.000

        // Call API
        $response = $this->getJson('/api/transaksi?tahun=2026&bulan=03');

        $response->assertStatus(200);
        
        // Assert: Cari mitra tersebut di response
        $mitraResult = collect($response->json())->firstWhere('id', $mitraId);
        
        $this->assertNotNull($mitraResult);
        // Harusnya 200.000 (Approved) saja, bukan 700.000
        $this->assertEquals(200000, $mitraResult['total_pendapatan']);
    }

    /**
     * TEST 3: Logika Limit Periode (Bulan vs All)
     * - Jika bulan spesifik: Limit = Batas Honor Dasar (misal 1jt)
     * - Jika bulan 'all': Limit = Batas Honor Dasar * 12 (misal 12jt)
     */
    public function test_limit_periode_berubah_sesuai_filter_bulan()
    {
        $this->authenticateAdmin();
        $batas = 1000000;
        AturanPeriode::create(['periode' => '2026', 'batas_honor' => $batas]);
        
        // Create dummy data agar array tidak kosong
        $this->createScenario('disetujui', '01'); 

        // Case A: Filter per Bulan
        $resBulan = $this->getJson('/api/transaksi?tahun=2026&bulan=01');
        $resBulan->assertStatus(200);
        $this->assertEquals($batas, $resBulan->json('0.limit_periode'));

        // Case B: Filter All (Tahunan)
        $resAll = $this->getJson('/api/transaksi?tahun=2026&bulan=all');
        $resAll->assertStatus(200);
        $this->assertEquals($batas * 12, $resAll->json('0.limit_periode'));
    }

    /**
     * TEST 4: Filter Dropdown (Get Approved Filters)
     * Endpoint ini harus mengembalikan struktur hirarki Kegiatan -> Subkegiatan
     * Hanya untuk yang status penugasannya 'disetujui'
     */
    public function test_get_approved_filters_structure()
    {
        $this->authenticateAdmin();

        // Data Approved (Harus Muncul)
        $scen1 = $this->createScenario('disetujui', '05');
        
        // Data Pending (Tidak Boleh Muncul)
        $scen2 = $this->createScenario('menunggu', '05');

        $response = $this->getJson('/api/transaksi/filters?tahun=2026');

        $response->assertStatus(200);
        
        $data = $response->json('data');

        // Pastikan Kegiatan Approved ada
        $this->assertTrue(collect($data)->contains('id', $scen1['kegiatan']->id));
        
        // Pastikan Kegiatan Pending TIDAK ada (jika berbeda ID kegiatan)
        if ($scen1['kegiatan']->id !== $scen2['kegiatan']->id) {
            $this->assertFalse(collect($data)->contains('id', $scen2['kegiatan']->id));
        }

        // Cek Struktur Subkegiatan
        $kegiatanRow = collect($data)->firstWhere('id', $scen1['kegiatan']->id);
        $this->assertArrayHasKey('subkegiatan', $kegiatanRow);
        $this->assertEquals($scen1['sub']->id, $kegiatanRow['subkegiatan'][0]['id']);
    }

    /**
     * TEST 5: Filter Lanjutan (By Kegiatan & Subkegiatan)
     */
    public function test_transaksi_filter_by_kegiatan_and_subkegiatan()
    {
        $this->authenticateAdmin();
        AturanPeriode::create(['periode' => '2026', 'batas_honor' => 5000000]);

        // Skenario: 2 Kegiatan berbeda
        $dataA = $this->createScenario('disetujui', '06'); // Kegiatan A
        $dataB = $this->createScenario('disetujui', '06'); // Kegiatan B

        // 1. Filter Kegiatan A
        $resA = $this->getJson("/api/transaksi?tahun=2026&bulan=06&kegiatan_id={$dataA['kegiatan']->id}");
        $resA->assertStatus(200);
        
        // Assert: Hanya Mitra A yang muncul
        $this->assertTrue(collect($resA->json())->contains('id', $dataA['mitra']->id));
        $this->assertFalse(collect($resA->json())->contains('id', $dataB['mitra']->id));

        // 2. Filter Subkegiatan B
        $resB = $this->getJson("/api/transaksi?tahun=2026&bulan=06&subkegiatan_id={$dataB['sub']->id}");
        $resB->assertStatus(200);

        // Assert: Hanya Mitra B yang muncul
        $this->assertFalse(collect($resB->json())->contains('id', $dataA['mitra']->id));
        $this->assertTrue(collect($resB->json())->contains('id', $dataB['mitra']->id));
    }
}