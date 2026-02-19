<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // Fungsi bantuan untuk membuat 'Admin' yang akan melakukan aksi
    private function authenticateAdmin()
    {
        $admin = User::create([
            'username' => 'admin_utama',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        // Simulasi bahwa si admin ini sedang login
        $this->actingAs($admin, 'sanctum');
    }

    /**
     * PENGUJIAN FUNGSI: store() 
     * Cabang 1: Gagal karena validasi
     */
    public function test_tambah_user_gagal_karena_validasi_kosong()
    {
        $this->authenticateAdmin(); // Panggil admin untuk login

        // Tembak API tanpa mengirim data apapun
        $response = $this->postJson('/api/users', []);

        // Ekspektasi: Kode 422 dan ada pesan error validasi
        $response->assertStatus(422)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonStructure(['errors' => ['username', 'email', 'password', 'role']]);
    }

    /**
     * PENGUJIAN FUNGSI: store() 
     * Cabang 2: Sukses menambah user
     */
    public function test_tambah_user_berhasil_masuk_database()
    {
        $this->authenticateAdmin();

        $dataBaru = [
            'username' => 'pegawai_baru',
            'email' => 'pegawai@example.com',
            'password' => 'rahasia123',
            'role' => 'user'
        ];

        $response = $this->postJson('/api/users', $dataBaru);

        // Ekspektasi 1: Status 201 Created
        $response->assertStatus(201)
                 ->assertJson(['status' => 'success', 'message' => 'User berhasil ditambahkan']);

        // Ekspektasi 2: Pastikan data benar-benar masuk ke database tabel 'user'
        $this->assertDatabaseHas('user', [
            'username' => 'pegawai_baru',
            'email' => 'pegawai@example.com',
            'role' => 'user'
        ]);
    }

    /**
     * PENGUJIAN FUNGSI: show() 
     * Cabang 1: Gagal karena ID tidak ada
     */
    public function test_lihat_detail_user_gagal_karena_not_found()
    {
        $this->authenticateAdmin();

        // Cari ID 999 yang pasti tidak ada di database
        $response = $this->getJson('/api/users/999');

        // Ekspektasi: Masuk ke blok if (!$user) dan return 404
        $response->assertStatus(404)
                 ->assertJson(['status' => 'error', 'message' => 'User tidak ditemukan']);
    }

    /**
     * PENGUJIAN FUNGSI: destroy() 
     * Cabang Sukses Hapus Data
     */
    public function test_hapus_user_berhasil()
    {
        $this->authenticateAdmin();

        // Buat 1 user tumbal untuk dihapus
        $userTumbal = User::create([
            'username' => 'tumbal',
            'email' => 'tumbal@example.com',
            'password' => '12345678',
            'role' => 'user'
        ]);

        // Eksekusi API hapus dengan ID user tumbal tersebut
        $response = $this->deleteJson('/api/users/' . $userTumbal->id);

        // Ekspektasi 1: Mengembalikan status 200
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success', 'message' => 'User berhasil dihapus']);

        // Ekspektasi 2: Pastikan data 'tumbal' sudah hilang dari database
        $this->assertDatabaseMissing('user', [
            'username' => 'tumbal'
        ]);
    }
}