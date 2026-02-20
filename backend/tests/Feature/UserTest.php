<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $this->actingAs($admin, 'sanctum');

        return $admin;
    }

    /* ==============================================================
       PENGUJIAN CRUD BASIC (INDEX, SHOW, ME)
    ============================================================== */

    public function test_get_all_users_berhasil()
    {
        $this->authenticateAdmin();
        User::create(['username' => 'user_biasa', 'email' => 'user@example.com', 'password' => bcrypt('pass'), 'role' => 'user']);

        $response = $this->getJson('/api/users');
        $response->assertStatus(200)->assertJsonStructure(['status', 'data']);
    }

    public function test_get_profile_me_berhasil()
    {
        $this->authenticateAdmin();
        $response = $this->getJson('/api/me');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_lihat_detail_user_berhasil_dan_not_found()
    {
        $admin = $this->authenticateAdmin();

        $resSukses = $this->getJson('/api/users/' . $admin->id);
        $resSukses->assertStatus(200)->assertJsonPath('status', 'success');

        $res404 = $this->getJson('/api/users/9999');
        $res404->assertStatus(404);
    }

    /* ==============================================================
       PENGUJIAN STORE & UPDATE
    ============================================================== */

    public function test_tambah_user_berhasil_dan_gagal()
    {
        $this->authenticateAdmin();

        // Gagal Validasi
        $resFail = $this->postJson('/api/users', []);
        $resFail->assertStatus(422)->assertJsonStructure(['errors' => ['username', 'email', 'password', 'role']]);

        // Sukses
        $resSukses = $this->postJson('/api/users', [
            'username' => 'pegawai_baru',
            'email' => 'pegawai@example.com',
            'password' => 'rahasia123',
            'role' => 'user'
        ]);
        $resSukses->assertStatus(201);
        $this->assertDatabaseHas('user', ['username' => 'pegawai_baru']);
    }

    public function test_update_user_berbagai_skenario()
    {
        $this->authenticateAdmin(); // Email: admin@example.com

        $user = User::create([
            'username' => 'user_lama',
            'email' => 'lama@example.com',
            'password' => bcrypt('pass123'),
            'role' => 'user'
        ]);

        // 1. Sukses Update TANPA ganti password
        $resTanpaPass = $this->putJson('/api/users/' . $user->id, [
            'username' => 'user_edit',
            'email' => 'lama@example.com',
            'role' => 'user'
        ]);
        $resTanpaPass->assertStatus(200);
        $this->assertDatabaseHas('user', ['username' => 'user_edit']);

        // 2. Sukses Update DENGAN ganti password
        $resDenganPass = $this->putJson('/api/users/' . $user->id, [
            'username' => 'user_edit_lagi',
            'email' => 'lama@example.com',
            'role' => 'user',
            'password' => 'passwordbaru'
        ]);
        $resDenganPass->assertStatus(200);
        $this->assertDatabaseHas('user', ['username' => 'user_edit_lagi']);

        // 3. Gagal Validasi Duplikat (Pakai email admin)
        $resDup = $this->putJson('/api/users/' . $user->id, [
            'username' => 'user_lama',
            'email' => 'admin@example.com',
            'role' => 'user'
        ]);
        $resDup->assertStatus(422);

        // 4. Not Found 404
        $res404 = $this->putJson('/api/users/9999', ['username' => 'A', 'email' => 'a@a.com', 'role' => 'user']);
        $res404->assertStatus(404);
    }

    public function test_hapus_user_berhasil_dan_not_found()
    {
        $this->authenticateAdmin();
        $user = User::create(['username' => 'tumbal', 'email' => 'tumbal@example.com', 'password' => bcrypt('pass'), 'role' => 'user']);

        $resSukses = $this->deleteJson('/api/users/' . $user->id);
        $resSukses->assertStatus(200);
        $this->assertDatabaseMissing('user', ['username' => 'tumbal']);

        $res404 = $this->deleteJson('/api/users/9999');
        $res404->assertStatus(404);
    }

    /* ==============================================================
       PENGUJIAN FITUR IMPORT EXCEL
    ============================================================== */

    public function test_import_user_gagal_validasi_file()
    {
        $this->authenticateAdmin();

        $resNoFile = $this->postJson('/api/users/import', []);
        $resNoFile->assertStatus(422);

        $filePdf = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');
        $resPdf = $this->postJson('/api/users/import', ['file' => $filePdf]);
        $resPdf->assertStatus(422);
    }

    public function test_import_user_file_kosong()
    {
        $this->authenticateAdmin();
        
        // File Excel Kosong (Hanya header)
        $csvContent = "username,email,password,role\n";
        $file = UploadedFile::fake()->createWithContent('empty.csv', $csvContent);

        $response = $this->postJson('/api/users/import', ['file' => $file]);
        $response->assertStatus(400)->assertJsonPath('message', 'File kosong atau format salah.');
    }

    public function test_import_user_berbagai_skenario_data()
    {
        $this->authenticateAdmin();

        User::create(['username' => 'duplikat', 'email' => 'duplikat@test.com', 'password' => bcrypt('pass'), 'role' => 'user']);

        // Skenario CSV lengkap
        $csv = "username,email,password,role\n";
        $csv .= "user_baru,baru@test.com,pass123,admin\n"; // Baris sukses
        $csv .= "duplikat,baru2@test.com,pass,user\n";     // Gagal duplikat username
        $csv .= "user3,duplikat@test.com,pass,user\n";     // Gagal duplikat email
        $csv .= ",,,\n";                                   // Skip baris kosong
        $csv .= "user_tanpa_pass,tanpapass@test.com,,mitra\n"; // Sukses (password default otomatis)

        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $response = $this->postJson('/api/users/import', ['file' => $file]);

        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $this->assertEquals(2, $response->json('successCount')); // user_baru & user_tanpa_pass
        $this->assertEquals(2, $response->json('failCount'));    // duplikat username & duplikat email

        $this->assertDatabaseHas('user', ['username' => 'user_baru', 'role' => 'admin']);
        $this->assertDatabaseHas('user', ['username' => 'user_tanpa_pass']);
    }

    public function test_import_user_gagal_baca_file_corrupt()
    {
        $this->authenticateAdmin();
        
        // Memasukkan isi teks sembarangan ke ekstensi xlsx untuk memicu \Exception dari IOFactory Excel
        $file = UploadedFile::fake()->createWithContent('corrupt.xlsx', 'ini bukan format excel yang valid');
        
        $response = $this->postJson('/api/users/import', ['file' => $file]);
        
        // Harus masuk ke catch (\Exception $e) dengan status 500
        $response->assertStatus(500)->assertJsonPath('status', 'error');
    }

    /* ==============================================================
       PENGUJIAN DOWNLOAD TEMPLATE
    ============================================================== */

    public function test_download_template_user_berhasil_dan_gagal()
    {
        $this->authenticateAdmin();
        $filePath = storage_path('app/import_users.xlsx');
        $backupPath = storage_path('app/import_users_backup.xlsx');

        // Tes 404 (Sembunyikan file asli)
        if (file_exists($filePath)) rename($filePath, $backupPath);
        
        $res404 = $this->get('/api/users/template');
        $res404->assertStatus(404);

        // Tes 200 (Buat file sementara)
        file_put_contents($filePath, 'dummy data');
        
        $res200 = $this->get('/api/users/template');
        $this->assertEquals(200, $res200->getStatusCode()); // Karena BinaryFileResponse

        // Kembalikan seperti semula
        unlink($filePath);
        if (file_exists($backupPath)) rename($backupPath, $filePath);
    }
    
    /* ==============================================================
       PENGUJIAN MODEL RELASI & CUSTOM METHOD (AGAR 100%)
    ============================================================== */

    public function test_model_user_helper_roles_berjalan_dengan_benar()
    {
        $admin = new User(['role' => 'admin']);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isSuperAdmin());
        $this->assertTrue($admin->hasRole('admin'));

        $superadmin = new User(['role' => 'superadmin']);
        $this->assertTrue($superadmin->isSuperAdmin());
        $this->assertFalse($superadmin->isAdmin());
        $this->assertTrue($superadmin->hasRole('superadmin'));
        
        $userBiasa = new User(['role' => 'user']);
        $this->assertFalse($userBiasa->isAdmin());
        $this->assertFalse($userBiasa->isSuperAdmin());
        $this->assertTrue($userBiasa->hasRole('user'));
    }

    
}