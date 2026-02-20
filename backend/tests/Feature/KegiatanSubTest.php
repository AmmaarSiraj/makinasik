<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kegiatan;
use App\Models\Subkegiatan;

class KegiatanSubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Mencegah error trigger ID di SQLite
        Subkegiatan::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = 'sub_' . mt_rand(1000, 9999);
            }
        });
    }

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_kegiatan',
            'email' => 'adminkegiatan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
    }

    /* ==============================================================
       PENGUJIAN KEGIATAN CONTROLLER
       ============================================================== */

    public function test_get_all_kegiatan_berhasil()
    {
        $this->authenticateAdmin();
        Kegiatan::create(['nama_kegiatan' => 'Kegiatan Index', 'deskripsi' => 'Deskripsi']);
        
        $response = $this->getJson('/api/kegiatan');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'data' => [['id', 'nama_kegiatan']]]);
    }

    public function test_tambah_kegiatan_sukses_dan_cek_not_found()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/kegiatan', [
            'nama_kegiatan' => 'Sensus Ekonomi 2026',
            'deskripsi' => 'Kegiatan sensus skala nasional'
        ]);
        $response->assertStatus(201)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('kegiatan', ['nama_kegiatan' => 'Sensus Ekonomi 2026']);

        // Gagal karena tidak ada di DB
        $errorResponse = $this->getJson('/api/kegiatan/99999');
        $errorResponse->assertStatus(404);
    }

    public function test_tambah_kegiatan_gagal_karena_validasi()
    {
        $this->authenticateAdmin();

        // Kosong akan ditolak oleh sistem validasi
        $response = $this->postJson('/api/kegiatan', []);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['nama_kegiatan']]);
    }

    public function test_lihat_detail_kegiatan_berhasil()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Detail']);
        
        $response = $this->getJson('/api/kegiatan/' . $kegiatan->id);
        $response->assertStatus(200)->assertJsonPath('data.nama_kegiatan', 'Kegiatan Detail');
    }

    public function test_update_kegiatan_berhasil_gagal_dan_not_found()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Lama']);
        
        // Sukses update
        $response = $this->putJson('/api/kegiatan/' . $kegiatan->id, ['nama_kegiatan' => 'Kegiatan Baru']);
        $response->assertStatus(200);
        $this->assertDatabaseHas('kegiatan', ['nama_kegiatan' => 'Kegiatan Baru']);

        // Gagal Validasi (kosong)
        $responseFail = $this->putJson('/api/kegiatan/' . $kegiatan->id, ['nama_kegiatan' => '']);
        $responseFail->assertStatus(422);

        // Not found (ID ngawur)
        $responseNotFound = $this->putJson('/api/kegiatan/99999', ['nama_kegiatan' => 'Tes']);
        $responseNotFound->assertStatus(404);
    }

    public function test_hapus_kegiatan_otomatis_menghapus_subkegiatan_di_bawahnya()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Induk']);
        
        Subkegiatan::create([
            'id_kegiatan' => $kegiatan->id,
            'nama_sub_kegiatan' => 'Sub Anak 1',
        ]);

        $response = $this->deleteJson('/api/kegiatan/' . $kegiatan->id);
        $response->assertStatus(200);

        // Pastikan cascade delete berhasil (Induk dan anak terhapus)
        $this->assertDatabaseMissing('kegiatan', ['id' => $kegiatan->id]);
        $this->assertDatabaseMissing('subkegiatan', ['nama_sub_kegiatan' => 'Sub Anak 1']);
    }

    public function test_hapus_kegiatan_gagal_not_found()
    {
        $this->authenticateAdmin();
        $response = $this->deleteJson('/api/kegiatan/99999');
        $response->assertStatus(404);
    }


    /* ==============================================================
       PENGUJIAN SUBKEGIATAN CONTROLLER
       ============================================================== */

    public function test_subkegiatan_index_dan_show()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan X']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub X']);
        
        // Test Index
        $respIndex = $this->getJson('/api/subkegiatan');
        $respIndex->assertStatus(200)->assertJsonStructure(['status', 'data']);

        // Test Show Sukses
        $respShow = $this->getJson('/api/subkegiatan/' . $sub->id);
        $respShow->assertStatus(200)->assertJsonPath('data.nama_sub_kegiatan', 'Sub X');

        // Test Show Gagal 404
        $respShowFail = $this->getJson('/api/subkegiatan/99999');
        $respShowFail->assertStatus(404);
    }

    public function test_subkegiatan_update_sukses_dan_gagal()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Y']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Y']);

        // Update Sukses
        $respUpdate = $this->putJson('/api/subkegiatan/' . $sub->id . '/info', [
            'nama_sub_kegiatan' => 'Sub Y Baru',
            'id_kegiatan' => $kegiatan->id
        ]);
        $respUpdate->assertStatus(200);
        $this->assertDatabaseHas('subkegiatan', ['nama_sub_kegiatan' => 'Sub Y Baru']);

        // Update Validasi Gagal (kosong)
        $respFail = $this->putJson('/api/subkegiatan/' . $sub->id . '/info', []);
        $respFail->assertStatus(422);

        // Update Not Found
        $respNotFound = $this->putJson('/api/subkegiatan/99999/info', ['nama_sub_kegiatan' => 'Z', 'id_kegiatan' => $kegiatan->id]);
        $respNotFound->assertStatus(404);
    }

    public function test_subkegiatan_get_by_kegiatan()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Z']);
        Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Sub Z1']);

        $resp = $this->getJson('/api/subkegiatan/kegiatan/' . $kegiatan->id);
        
        // Cukup pastikan status 200 OK
        $resp->assertStatus(200);
        
        // Pastikan response adalah array dan elemen pertamanya punya nama 'Sub Z1'
        $this->assertEquals('Sub Z1', $resp->json('0.nama_sub_kegiatan'));
    }

    public function test_subkegiatan_destroy_not_found()
    {
         $this->authenticateAdmin();
         $resp = $this->deleteJson('/api/subkegiatan/99999');
         $resp->assertStatus(404);
    }

    public function test_validasi_tanggal_subkegiatan_gagal_jika_mundur()
    {
        $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus Pertanian']);

        $response = $this->postJson('/api/subkegiatan', [
            'id_kegiatan' => $kegiatan->id,
            'nama_sub_kegiatan' => 'Pencacahan Lapangan',
            'tanggal_mulai' => '2026-05-15',
            'tanggal_selesai' => '2026-05-10' 
        ]);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['tanggal_selesai']]);
    }

    public function test_import_csv_kegiatan_sukses_konversi_tanggal_dd_mm_yyyy()
    {
        $this->authenticateAdmin();

        $csvContent = implode("\n", [
            "nama_kegiatan,nama_sub_kegiatan,deskripsi,tanggal_mulai,tanggal_selesai,jabatan,tarif,satuan,basis_volume,beban_anggaran",
            "Sensus Penduduk 2026,Pencacahan Lapangan,Deskripsi Testing,15/08/2026,20/08/2026,,,,,"
        ]);

        $file = UploadedFile::fake()->createWithContent('data_kegiatan.csv', $csvContent);

        $response = $this->postJson('/api/subkegiatan/import', ['file' => $file]);

        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('kegiatan', ['nama_kegiatan' => 'Sensus Penduduk 2026']);
        $this->assertDatabaseHas('subkegiatan', ['nama_sub_kegiatan' => 'Pencacahan Lapangan']);
    }

    /* ==============================================================
       PENGUJIAN MODEL RELASI (AGAR 100%)
       ============================================================== */

    public function test_model_kegiatan_relation_subkegiatan()
    {
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Kegiatan Induk Uji Relasi']);
        Subkegiatan::create([
            'id_kegiatan' => $kegiatan->id,
            'nama_sub_kegiatan' => 'Subkegiatan Uji Relasi',
        ]);

        $relasiSubkegiatan = $kegiatan->subkegiatan;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $relasiSubkegiatan);
        $this->assertInstanceOf(Subkegiatan::class, $relasiSubkegiatan->first());
        $this->assertEquals($kegiatan->id, $relasiSubkegiatan->first()->id_kegiatan);
    }
}