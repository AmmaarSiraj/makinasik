<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SatuanKegiatan;

class SatuanKegiatanTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_satuan',
            'email' => 'adminsatuan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
    }

    /**
     * PENGUJIAN FUNGSI: store() -> Jalur Sukses dan Validasi Duplikat (422)
     */
    public function test_tambah_satuan_sukses_dan_gagal_jika_duplikat()
    {
        $this->authenticateAdmin();

        // 1. Sukses menambah Satuan Pertama
        $response1 = $this->postJson('/api/satuan-kegiatan', [
            'nama_satuan' => 'Orang Bulan',
            'alias' => 'OB'
        ]);

        $response1->assertStatus(201)
                  ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('satuan_kegiatan', ['nama_satuan' => 'Orang Bulan']);

        // 2. Gagal karena nama_satuan harus UNIQUE
        $response2 = $this->postJson('/api/satuan-kegiatan', [
            'nama_satuan' => 'Orang Bulan', // Sengaja diduplikat
            'alias' => 'OB-2'
        ]);

        $response2->assertStatus(422)
                  ->assertJsonStructure(['errors' => ['nama_satuan']]);
    }

    /**
     * PENGUJIAN FUNGSI: update() -> Pengecualian Validasi Unique untuk ID sendiri
     */
    public function test_update_satuan_mengabaikan_unique_untuk_id_sendiri()
    {
        $this->authenticateAdmin();

        $satuanA = SatuanKegiatan::create(['nama_satuan' => 'Dokumen', 'alias' => 'DOK']);
        $satuanB = SatuanKegiatan::create(['nama_satuan' => 'Paket', 'alias' => 'PKT']);

        // 1. Tes Update Gagal: Satuan A mencoba memakai nama Satuan B
        $failResponse = $this->putJson('/api/satuan-kegiatan/' . $satuanA->id, [
            'nama_satuan' => 'Paket', 
            'alias' => 'DOK-Baru'
        ]);
        $failResponse->assertStatus(422)
                     ->assertJsonStructure(['errors' => ['nama_satuan']]);

        // 2. Tes Update Sukses: Satuan A di-save ulang TANPA mengubah nama_satuan
        $successResponse = $this->putJson('/api/satuan-kegiatan/' . $satuanA->id, [
            'nama_satuan' => 'Dokumen', 
            'alias' => 'DOK-Baru' // Hanya alias yang diubah
        ]);
        
        $successResponse->assertStatus(200)
                        ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('satuan_kegiatan', [
            'id' => $satuanA->id,
            'alias' => 'DOK-Baru'
        ]);
    }

    /**
     * PENGUJIAN FUNGSI: index() dan show()
     */
    public function test_lihat_semua_dan_detail_satuan_serta_cek_404()
    {
        $this->authenticateAdmin();

        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Responden', 'alias' => 'RSP']);

        // 1. Tes Index (Lihat Semua)
        $responseIndex = $this->getJson('/api/satuan-kegiatan');
        $responseIndex->assertStatus(200)
                      ->assertJsonPath('status', 'success');

        // 2. Tes Show (Detail)
        $responseShow = $this->getJson('/api/satuan-kegiatan/' . $satuan->id);
        $responseShow->assertStatus(200)
                     ->assertJsonPath('data.nama_satuan', 'Responden');

        // 3. Tes Show ID Palsu (Gagal 404)
        $responseShowFail = $this->getJson('/api/satuan-kegiatan/999');
        $responseShowFail->assertStatus(404)
                         ->assertJsonPath('message', 'Data tidak ditemukan');
    }

    /**
     * PENGUJIAN FUNGSI: destroy()
     */
    public function test_hapus_satuan_sukses_dan_gagal_404()
    {
        $this->authenticateAdmin();

        $satuan = SatuanKegiatan::create(['nama_satuan' => 'Buku', 'alias' => 'BKS']);

        // 1. Tes Delete Sukses
        $responseDelete = $this->deleteJson('/api/satuan-kegiatan/' . $satuan->id);
        
        $responseDelete->assertStatus(200)
                       ->assertJsonPath('message', 'Satuan kegiatan berhasil dihapus');

        $this->assertDatabaseMissing('satuan_kegiatan', ['id' => $satuan->id]);

        // 2. Tes Delete ID Palsu (Gagal 404)
        $responseDeleteFail = $this->deleteJson('/api/satuan-kegiatan/999');
        $responseDeleteFail->assertStatus(404)
                           ->assertJsonPath('message', 'Data tidak ditemukan');
    }
}