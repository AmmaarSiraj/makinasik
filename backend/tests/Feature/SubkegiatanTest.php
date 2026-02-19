<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;

class SubkegiatanTest extends TestCase
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
            'username' => 'admin_sub',
            'email' => 'adminsub@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
    }

    /**
     * PENGUJIAN: getByKegiatan() -> Filter relasi One-to-Many
     */
    public function test_filter_subkegiatan_berdasarkan_id_kegiatan_berhasil()
    {
        $this->authenticateAdmin();

        $kegiatanA = Kegiatan::create(['nama_kegiatan' => 'Sensus A']);
        $kegiatanB = Kegiatan::create(['nama_kegiatan' => 'Survei B']);

        Subkegiatan::create(['id_kegiatan' => $kegiatanA->id, 'nama_sub_kegiatan' => 'Subkegiatan Milik A 1']);
        Subkegiatan::create(['id_kegiatan' => $kegiatanA->id, 'nama_sub_kegiatan' => 'Subkegiatan Milik A 2']);
        Subkegiatan::create(['id_kegiatan' => $kegiatanB->id, 'nama_sub_kegiatan' => 'Subkegiatan Milik B 1']);

        // Jika rute ini ternyata berbeda, mohon disesuaikan juga ya
        $response = $this->getJson('/api/subkegiatan/kegiatan/' . $kegiatanA->id);

        $response->assertStatus(200);

        $response->assertJsonCount(2); 
        $this->assertStringContainsString('Subkegiatan Milik A', $response->json('0.nama_sub_kegiatan'));
        $this->assertStringNotContainsString('Subkegiatan Milik B', $response->getContent());
    }

    /**
     * PENGUJIAN: downloadTemplate() -> 404 (File Tidak Ada)
     */
   public function test_download_template_gagal_karena_file_tidak_ada()
    {
        $this->authenticateAdmin();

        $filePath = storage_path('app/template_import_kegiatan.xlsx');
        $backupPath = storage_path('app/template_import_kegiatan_backup.xlsx');

        // AMAN: Jika file asli milik Anda ada, kita "sembunyikan" (rename) sementara
        if (file_exists($filePath)) {
            rename($filePath, $backupPath);
        }

        $response = $this->getJson('/api/subkegiatan/template');

        $response->assertStatus(404)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'File template belum tersedia di server.');

        // AMAN: Setelah tes 404 selesai, kita kembalikan file asli Anda!
        if (file_exists($backupPath)) {
            rename($backupPath, $filePath);
        }
    }

    /**
     * PENGUJIAN: downloadTemplate() -> 200 (Berhasil Download)
     */
    public function test_download_template_sukses()
    {
        $this->authenticateAdmin();

        $filePath = storage_path('app/template_import_kegiatan.xlsx');
        $isDummyCreated = false;

        // Jika Anda belum sempat mengembalikan file aslinya, tes ini akan meminjamkan file sementara
        if (!file_exists($filePath)) {
            file_put_contents($filePath, 'Ini dummy sementara');
            $isDummyCreated = true;
        }

        $response = $this->get('/api/subkegiatan/template');

        $response->assertStatus(200)
                 ->assertDownload('template_import_kegiatan.xlsx');

        // AMAN: Hanya hapus file jika itu adalah file dummy buatan PHPUnit. 
        // File asli milik Anda TIDAK AKAN DIHAPUS.
        if ($isDummyCreated) {
            unlink($filePath);
        }
    }
}