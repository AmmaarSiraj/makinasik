<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /* ==========================================
       PENGUJIAN FITUR LOGIN
    ========================================== */

    public function test_login_gagal_karena_password_salah()
    {
        User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);

        $response = $this->postJson('/api/users/login', [
            'email' => 'test@example.com',
            'password' => 'password_salah' 
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Username, Email, atau Password salah.'
                 ]);
    }

    public function test_login_berhasil_mendapatkan_token()
    {
        User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);

        $response = $this->postJson('/api/users/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([ 
                     'status',
                     'message',
                     'access_token',
                     'user' => ['id', 'username', 'email', 'role']
                 ]);
    }

    public function test_login_menggunakan_username_berhasil()
    {
        User::create([
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);

        // Kirim 'username' pada request body alih-alih email
        $response = $this->postJson('/api/users/login', [
            'username' => 'johndoe',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token']);
    }

    public function test_login_gagal_karena_identifier_kosong()
    {
        // Sengaja tidak mengirim email atau username
        $response = $this->postJson('/api/users/login', [
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['email']]);
    }


    /* ==========================================
       PENGUJIAN FITUR REGISTER
    ========================================== */

    public function test_register_berhasil()
    {
        $response = $this->postJson('/api/register', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['access_token']);
        
        $this->assertDatabaseHas('user', [
            'email' => 'newuser@example.com',
            'username' => 'newuser'
        ]);
    }

    public function test_register_gagal_karena_validasi_kosong()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['username', 'email', 'password']]);
    }

    public function test_register_gagal_karena_email_duplikat()
    {
        User::create([
            'username' => 'existinguser',
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);

        // Coba daftar menggunakan email yang sudah ada di database
        $response = $this->postJson('/api/register', [
            'username' => 'anotheruser',
            'email' => 'existing@example.com', 
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['email']]);
    }


    /* ==========================================
       PENGUJIAN FITUR LOGOUT
    ========================================== */

    public function test_logout_berhasil()
    {
        $user = User::create([
            'username' => 'logoutuser',
            'email' => 'logout@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);

        // Buat token sanctum secara manual
        $token = $user->createToken('auth_token')->plainTextToken;

        // Panggil API logout dengan membawa token di header
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success', 'message' => 'Berhasil logout.']);
    }

    public function test_logout_gagal_karena_tidak_ada_token()
    {
        // Tembak logout tanpa login terlebih dahulu (tanpa header Authorization)
        $response = $this->postJson('/api/logout');

        // Middleware auth:sanctum akan langsung memblokir dan mengembalikan status 401 (Unauthenticated)
        $response->assertStatus(401);
    }
}