<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;
use App\Models\JabatanMitra;
use App\Models\SatuanKegiatan;
use App\Models\Honorarium;

class SubkegiatanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass Trigger SQLite untuk ID Subkegiatan
        Subkegiatan::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'sub_' . mt_rand(10000, 99999);
            }
        });
    }

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_sub',
            'email' => 'adminsub@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
        return $admin;
    }

    /* ==============================================================
       PENGUJIAN CRUD BASIC (INDEX, STORE, SHOW, UPDATE, DESTROY)
    ============================================================== */

    public function test_index_subkegiatan_berhasil()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Index']);
        Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Index']);

        $response = $this->getJson('/api/subkegiatan');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_store_subkegiatan_berhasil_dan_validasi_gagal()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Store']);

        // Sukses
        $response = $this->postJson('/api/subkegiatan', [
            'id_kegiatan' => $kegiatan->id,
            'nama_sub_kegiatan' => 'Sub Baru Store',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-10',
            'status' => 'pending'
        ]);
        $response->assertStatus(201)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('subkegiatan', ['nama_sub_kegiatan' => 'Sub Baru Store']);

        // Gagal Validasi
        $resFail = $this->postJson('/api/subkegiatan', []);
        $resFail->assertStatus(422)->assertJsonStructure(['errors' => ['id_kegiatan', 'nama_sub_kegiatan']]);
    }

    public function test_show_subkegiatan_berhasil_dan_not_found()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Show']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Show']);

        $resSukses = $this->getJson('/api/subkegiatan/' . $sub->id);
        $resSukses->assertStatus(200)->assertJsonPath('status', 'success');

        $res404 = $this->getJson('/api/subkegiatan/sub_99999');
        $res404->assertStatus(404);
    }

    public function test_update_subkegiatan_berhasil_validasi_gagal_dan_not_found()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Upd']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Lama']);

        // 1. Sukses (Pastikan ada /info di URL)
        $resSukses = $this->putJson('/api/subkegiatan/' . $sub->id . '/info', [
            'nama_sub_kegiatan' => 'Sub Baru Diupdate',
            'tanggal_mulai' => '2026-05-05',
            'tanggal_selesai' => '2026-05-15'
        ]);
        $resSukses->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('subkegiatan', ['nama_sub_kegiatan' => 'Sub Baru Diupdate']);

        // 2. Gagal Validasi (Pastikan ada /info di URL)
        $resVal = $this->putJson('/api/subkegiatan/' . $sub->id . '/info', [
            'nama_sub_kegiatan' => 'A',
            'tanggal_mulai' => '2026-05-15',
            'tanggal_selesai' => '2026-05-05' // Lebih dulu dari tgl mulai
        ]);
        $resVal->assertStatus(422);

        // 3. Not Found (Pastikan ada /info di URL)
        $res404 = $this->putJson('/api/subkegiatan/sub_99999/info', ['nama_sub_kegiatan' => 'X']);
        $res404->assertStatus(404);
    }

    public function test_destroy_subkegiatan_berhasil_dan_not_found()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Del']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Delete']);

        $resSukses = $this->deleteJson('/api/subkegiatan/' . $sub->id);
        $resSukses->assertStatus(200)->assertJsonPath('status', 'success');

        $res404 = $this->deleteJson('/api/subkegiatan/sub_99999');
        $res404->assertStatus(404);
    }


    /* ==============================================================
       PENGUJIAN KUSTOM (GET BY KEGIATAN & DOWNLOAD TEMPLATE)
    ============================================================== */

    public function test_filter_subkegiatan_berdasarkan_id_kegiatan_berhasil()
    {
        $this->authenticateAdmin();
        $kegiatanA = Kegiatan::create(['nama_kegiatan' => 'Sensus A']);
        $kegiatanB = Kegiatan::create(['nama_kegiatan' => 'Survei B']);

        Subkegiatan::create(['id_kegiatan' => $kegiatanA->id, 'nama_sub_kegiatan' => 'Sub A1']);
        Subkegiatan::create(['id_kegiatan' => $kegiatanB->id, 'nama_sub_kegiatan' => 'Sub B1']);

        $response = $this->getJson('/api/subkegiatan/kegiatan/' . $kegiatanA->id);
        $response->assertStatus(200);
        $response->assertJsonCount(1); 
        $this->assertEquals('Sub A1', $response->json('0.nama_sub_kegiatan'));
    }

    public function test_download_template_gagal_karena_file_tidak_ada()
    {
        $this->authenticateAdmin();
        $filePath = storage_path('app/template_import_kegiatan.xlsx');
        $backupPath = storage_path('app/template_import_kegiatan_backup.xlsx');

        if (file_exists($filePath)) rename($filePath, $backupPath);

        $response = $this->getJson('/api/subkegiatan/template');
        $response->assertStatus(404)->assertJsonPath('status', 'error');

        if (file_exists($backupPath)) rename($backupPath, $filePath);
    }

    public function test_download_template_sukses()
    {
        $this->authenticateAdmin();
        $filePath = storage_path('app/template_import_kegiatan.xlsx');
        $isDummyCreated = false;

        if (!file_exists($filePath)) {
            file_put_contents($filePath, 'Ini dummy sementara');
            $isDummyCreated = true;
        }

        $response = $this->get('/api/subkegiatan/template');
        $response->assertStatus(200)->assertDownload('template_import_kegiatan.xlsx');

        if ($isDummyCreated) unlink($filePath);
    }


    /* ==============================================================
       PENGUJIAN IMPORT EXCEL (COVER SEMUA CABANG LOGIKA)
    ============================================================== */

    public function test_import_gagal_validasi_file()
    {
        $this->authenticateAdmin();

        // Coba tanpa file
        $resNoFile = $this->postJson('/api/subkegiatan/import', []);
        $resNoFile->assertStatus(422);

        // Coba file format salah (pdf)
        $filePdf = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');
        $resPdf = $this->postJson('/api/subkegiatan/import', ['file' => $filePdf]);
        $resPdf->assertStatus(422);
    }

    public function test_import_gagal_karena_file_kosong_atau_header_salah()
    {
        $this->authenticateAdmin();

        // Header salah (tidak ada keyword 'nama_kegiatan')
        $csvContent = "Kolom A,Kolom B\nIsi A,Isi B";
        $file = UploadedFile::fake()->createWithContent('data.csv', $csvContent);

        $response = $this->postJson('/api/subkegiatan/import', ['file' => $file]);
        $response->assertStatus(500); // Controller melempar exception 'Header tidak ditemukan'
    }

    public function test_import_sukses_berbagai_skenario_dan_format_tanggal()
    {
        $this->authenticateAdmin();

        // Siapkan Master Data
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah']);
        SatuanKegiatan::create(['nama_satuan' => 'Orang Hari']);

        $kegExisting = Kegiatan::create(['nama_kegiatan' => 'Keg Lama']);
        Subkegiatan::create([
            'id_kegiatan' => $kegExisting->id,
            'nama_sub_kegiatan' => 'Sub Lama',
            'tanggal_mulai' => '2020-01-01',
            'tanggal_selesai' => '2020-01-02'
        ]);

        // Buat Skenario CSV yang akan memicu SEMUA cabang di fungsi import
        $csv = "nama_kegiatan,nama_sub_kegiatan,deskripsi,tanggal_mulai,tanggal_selesai,jabatan,tarif,satuan,basis_volume,beban_anggaran\n";
        
        // Baris 1: Update data existing (Format Database Y-m-d)
        $csv .= "Keg Lama,Sub Lama,Update Desc,2026-01-01,2026-01-10,Pencacah,100000,Orang Hari,5,APBN\n";
        
        // Baris 2: Tambah Data Baru + Format Tanggal Excel numerik + Regex Jabatan dgn kurung
        $csv .= "Keg Baru,Sub Baru,Baru Desc,45000,45005,Pencacah (PCL),150000,Orang Hari,10,APBD\n";
        
        // Baris 3: Tambah Data Baru + Format Tanggal dd/mm/yyyy 
        $csv .= "Keg Tiga,Sub Tiga,Desc Tiga,15/08/2026,20/08/2026,PCL,50000,Orang Hari,1,BPS\n";
        
        // Baris 4: Baris kosong (kegiatan dan subkegiatan kosong) -> Harus di-skip
        $csv .= ",,,,,,,,,\n";

        // Baris 5: Error (Format tanggal ngawur, trigger Exception di formatDate, Subkegiatan Batal Dibuat)
        $csv .= "Keg Err,Sub Err,Err Desc,tanggal-salah,2026-01-01,PCL,10000,Orang Hari,1,\n";

        // Baris 6: Error Jabatan, tapi Subkegiatan TETAP berhasil dibuat oleh controller
        $csv .= "Keg Jab,Sub Jab,,2026-01-01,2026-01-02,Jabatan Aneh,100,Orang Hari,1,\n";

        $file = UploadedFile::fake()->createWithContent('import_kompleks.csv', $csv);

        $response = $this->postJson('/api/subkegiatan/import', ['file' => $file]);

        $response->assertStatus(200)->assertJsonPath('status', 'success');

        // PERBAIKAN: Kalkulasi sukses ada 4 (Baris 1, 2, 3, dan 6 berhasil membuat Subkegiatan)
        $this->assertEquals(4, $response->json('successCount')); 
        // FailCount dihitung dari jumlah pesan error di array (Baris 5 Exception, Baris 6 Jabatan Error)
        $this->assertEquals(2, $response->json('failCount'));    

        // Verifikasi Update Berhasil
        $this->assertDatabaseHas('subkegiatan', ['nama_sub_kegiatan' => 'Sub Lama', 'deskripsi' => 'Update Desc']);
        
        // Verifikasi Create Berhasil
        $this->assertDatabaseHas('subkegiatan', ['nama_sub_kegiatan' => 'Sub Baru']);
        $this->assertDatabaseHas('subkegiatan', ['nama_sub_kegiatan' => 'Sub Tiga']);
        $this->assertDatabaseHas('subkegiatan', ['nama_sub_kegiatan' => 'Sub Jab']); // Bukti baris 6 sukses terbuat
    }
}