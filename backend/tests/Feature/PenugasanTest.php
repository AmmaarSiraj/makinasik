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

    /**
     * ==========================================
     * PENGUJIAN 1: STORE PENUGASAN (BESERTA ANGGOTA)
     * ==========================================
     */
    public function test_buat_penugasan_baru_beserta_anggota_berhasil()
    {
        $admin = $this->authenticateAdmin();

        // 1. Siapkan Data Master Pendukung
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan']);
        
        $mitra1 = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '111', 'nomor_hp' => '081']);
        $mitra2 = Mitra::create(['nama_lengkap' => 'Siti', 'nik' => '222', 'nomor_hp' => '082']);
        
        $jabatanPML = JabatanMitra::create(['kode_jabatan' => 'PML', 'nama_jabatan' => 'Pengawas Lapangan']);
        $jabatanPCL = JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah Lapangan']);

        // 2. Tembak Endpoint POST /api/penugasan
        $payload = [
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id,
            'anggota'        => [
                [
                    'id_mitra'     => $mitra1->id,
                    'kode_jabatan' => $jabatanPML->kode_jabatan,
                    'volume_tugas' => 1
                ],
                [
                    'id_mitra'     => $mitra2->id,
                    'kode_jabatan' => $jabatanPCL->kode_jabatan,
                    'volume_tugas' => 2
                ]
            ]
        ];

        $response = $this->postJson('/api/penugasan', $payload);

        // 3. Verifikasi Keberhasilan API
        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        // 4. Verifikasi Database
        $this->assertDatabaseHas('penugasan', [
            'id_subkegiatan'   => $sub->id,
            'id_pengawas'      => $admin->id,
            'status_penugasan' => 'menunggu'
        ]);

        $penugasanId = $response->json('data.id_penugasan');
        $this->assertDatabaseHas('kelompok_penugasan', [
            'id_penugasan' => $penugasanId,
            'id_mitra'     => $mitra1->id,
            'volume_tugas' => 1
        ]);
        $this->assertDatabaseHas('kelompok_penugasan', [
            'id_penugasan' => $penugasanId,
            'id_mitra'     => $mitra2->id,
            'volume_tugas' => 2
        ]);
    }

    /**
     * ==========================================
     * PENGUJIAN 2: UJI VALIDASI STORE (FAIL DB TRANSACTION)
     * ==========================================
     */
    public function test_buat_penugasan_gagal_jika_format_anggota_salah_422()
    {
        $admin = $this->authenticateAdmin();
        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan']);

        $payload = [
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id,
            'anggota'        => [
                [
                    'id_mitra'     => 9999, // Mitra Palsu
                    'kode_jabatan' => 'PCL',
                    'volume_tugas' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/penugasan', $payload);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['anggota.0.id_mitra']]);

        $this->assertDatabaseCount('penugasan', 0);
    }

    /**
     * ==========================================
     * PENGUJIAN 3: GET BY MITRA & PERIODE
     * ==========================================
     */
    public function test_get_penugasan_by_mitra_and_periode()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create([
            'id_kegiatan' => $kegiatan->id, 
            'nama_sub_kegiatan' => 'Pencacahan',
            'tanggal_mulai' => '2026-08-10', 
            'tanggal_selesai' => '2026-08-30'
        ]);
        
        $mitra = Mitra::create(['nama_lengkap' => 'Budi', 'nik' => '111', 'nomor_hp' => '081']);
        
        JabatanMitra::create(['kode_jabatan' => 'PCL', 'nama_jabatan' => 'Pencacah Lapangan']);

        $penugasan = Penugasan::create([
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id,
            'status_penugasan' => 'disetujui' 
        ]);

        KelompokPenugasan::create([
            'id_penugasan' => $penugasan->id,
            'id_mitra'     => $mitra->id,
            'kode_jabatan' => 'PCL', 
            'volume_tugas' => 1
        ]);

        $response = $this->getJson('/api/penugasan/mitra/' . $mitra->id . '/periode/2026-08');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Sensus 2026', $response->json('data.0.nama_kegiatan'));
    }

    /**
     * ==========================================
     * PENGUJIAN 4: PREVIEW IMPORT (EMPTY EXCEL)
     * ==========================================
     */
    public function test_preview_import_menolak_file_excel_kosong()
    {
        $this->authenticateAdmin();
        Excel::fake(); 

        $file = UploadedFile::fake()->create('penugasan.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->postJson('/api/penugasan/preview-import', [
            'file' => $file
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'File kosong atau format salah.');
    }

    /**
     * ==========================================
     * PENGUJIAN 5: UPDATE STATUS (FRONTEND TOGGLE)
     * ==========================================
     */
    public function test_update_status_penugasan_berhasil_di_toggle()
    {
        $admin = $this->authenticateAdmin();

        $kegiatan = Kegiatan::create(['nama_kegiatan' => 'Sensus 2026']);
        $sub = Subkegiatan::create(['id_kegiatan' => $kegiatan->id, 'nama_sub_kegiatan' => 'Pencacahan']);
        
        $penugasan = Penugasan::create([
            'id_subkegiatan' => $sub->id,
            'id_pengawas'    => $admin->id,
            'status_penugasan' => 'menunggu'
        ]);

        $response1 = $this->putJson('/api/penugasan/' . $penugasan->id, [
            'status_penugasan' => 'disetujui'
        ]);

        $response1->assertStatus(200)
                  ->assertJsonPath('status', 'success')
                  ->assertJsonPath('data.status_penugasan', 'disetujui'); 

        $this->assertDatabaseHas('penugasan', [
            'id' => $penugasan->id,
            'status_penugasan' => 'disetujui'
        ]);

        $response2 = $this->putJson('/api/penugasan/' . $penugasan->id, [
            'status_penugasan' => 'menunggu'
        ]);

        $response2->assertStatus(200);

        $this->assertDatabaseHas('penugasan', [
            'id' => $penugasan->id,
            'status_penugasan' => 'menunggu' 
        ]);
    }
}