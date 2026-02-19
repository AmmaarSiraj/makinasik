<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_gagal_karena_password_salah()
    {
        $user = User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);

        // UBAH URL DI SINI JADI /api/users/login
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
        $user = User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);

        // UBAH URL DI SINI JUGA JADI /api/users/login
        $response = $this->postJson('/api/users/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([ 
                     'status',
                     'message',
                     'access_token',
                     'user' => [
                         'id', 'username', 'email', 'role'
                     ]
                 ]);
    }
}