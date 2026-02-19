<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AturanPeriode;

class AturanPeriodeTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_aturan',
            'email' => 'adminaturan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        $this->actingAs($admin, 'sanctum');
    }

    /**
     * PENGUJIAN FUNGSI: store() -> Sukses dan Jebakan Validasi
     */
    public function test_tambah_aturan_sukses_dan_gagal_karena_validasi_ketat()
    {
        $this->authenticateAdmin();

        // 1. Skenario Sukses
        $response1 = $this->postJson('/api/aturan-periode', [
            'periode' => '2026-01',
            'batas_honor' => 3500000
        ]);
        
        $response1->assertStatus(201)
                  ->assertJsonPath('status', 'success');
        
        $this->assertDatabaseHas('aturan_periode', [
            'periode' => '2026-01',
            'batas_honor' => 3500000
        ]);

        // 2. Skenario Gagal: Duplikat Periode
        $response2 = $this->postJson('/api/aturan-periode', [
            'periode' => '2026-01', // Sengaja diduplikat
            'batas_honor' => 4000000
        ]);
        
        $response2->assertStatus(422)
                  ->assertJsonStructure(['errors' => ['periode']]);

        // 3. Skenario Gagal: Batas Honor Minus (Menguji rule min:0)
        $response3 = $this->postJson('/api/aturan-periode', [
            'periode' => '2026-02',
            'batas_honor' => -50000 // Uang tidak boleh minus
        ]);
        
        $response3->assertStatus(422)
                  ->assertJsonStructure(['errors' => ['batas_honor']]);
    }

    /**
     * PENGUJIAN FUNGSI: update() -> Pengecualian Validasi Unique untuk ID sendiri
     */
    public function test_update_aturan_mengabaikan_unique_untuk_id_sendiri()
    {
        $this->authenticateAdmin();

        $aturanA = AturanPeriode::create(['periode' => '2026-03', 'batas_honor' => 1000000]);
        $aturanB = AturanPeriode::create(['periode' => '2026-04', 'batas_honor' => 2000000]);

        // 1. Tes Update Gagal: Aturan A mencoba memakai periode Aturan B
        $failResponse = $this->putJson('/api/aturan-periode/' . $aturanA->id, [
            'periode' => '2026-04',
            'batas_honor' => 1500000
        ]);
        
        $failResponse->assertStatus(422)
                     ->assertJsonStructure(['errors' => ['periode']]);

        // 2. Tes Update Sukses: Aturan A diperbarui tanpa mengubah periode-nya
        $successResponse = $this->putJson('/api/aturan-periode/' . $aturanA->id, [
            'periode' => '2026-03',
            'batas_honor' => 5000000 // Hanya mengubah nominal uang
        ]);
        
        $successResponse->assertStatus(200)
                        ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('aturan_periode', [
            'id' => $aturanA->id,
            'batas_honor' => 5000000
        ]);
    }

    /**
     * PENGUJIAN FUNGSI: index(), show(), destroy(), dan 404
     */
    public function test_semua_akses_data_dan_validasi_not_found_404()
    {
        $this->authenticateAdmin();

        $aturan = AturanPeriode::create(['periode' => '2026-05', 'batas_honor' => 4000000]);

        // 1. Tes Index
        $responseIndex = $this->getJson('/api/aturan-periode');
        $responseIndex->assertStatus(200)
                      ->assertJsonPath('status', 'success');

        // 2. Tes Show
        $responseShow = $this->getJson('/api/aturan-periode/' . $aturan->id);
        $responseShow->assertStatus(200)
                     ->assertJsonPath('data.periode', '2026-05');

        // 3. Tes Destroy
        $responseDelete = $this->deleteJson('/api/aturan-periode/' . $aturan->id);
        $responseDelete->assertStatus(200)
                       ->assertJsonPath('message', 'Aturan periode berhasil dihapus');
        
        $this->assertDatabaseMissing('aturan_periode', ['id' => $aturan->id]);

        // 4. BOMBARDIR 404! Memastikan semua endpoint menolak ID yang tidak ada
        $this->getJson('/api/aturan-periode/999')
             ->assertStatus(404)->assertJsonPath('message', 'Data tidak ditemukan');
             
        $this->putJson('/api/aturan-periode/999', ['periode' => '2026-99', 'batas_honor' => 0])
             ->assertStatus(404)->assertJsonPath('message', 'Data tidak ditemukan');
             
        $this->deleteJson('/api/aturan-periode/999')
             ->assertStatus(404)->assertJsonPath('message', 'Data tidak ditemukan');
    }
}