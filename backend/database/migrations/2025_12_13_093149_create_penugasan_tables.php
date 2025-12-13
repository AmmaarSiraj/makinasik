<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Penugasan
        Schema::create('penugasan', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Subkegiatan (ID String)
            $table->string('id_subkegiatan', 50);
            $table->foreign('id_subkegiatan')
                  ->references('id')->on('subkegiatan')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Relasi ke Pengawas (User)
            $table->foreignId('id_pengawas')
                  ->constrained('users')
                  ->onDelete('cascade'); // Jika user dihapus, penugasan ikut hilang

            $table->timestamps();
        });

        // 2. Tabel Kelompok Penugasan
        Schema::create('kelompok_penugasan', function (Blueprint $table) {
            $table->id();

            // Relasi ke Penugasan
            $table->foreignId('id_penugasan')
                  ->constrained('penugasan')
                  ->onDelete('cascade'); // Jika surat tugas dihapus, kelompok hilang

            // Relasi ke Mitra (Asumsi tabel 'mitra' sudah ada, jika belum, buat dulu!)
            // Jika mitra diambil dari tabel users, ganti 'mitra' jadi 'users'
            $table->integer('id_mitra'); 
            // $table->foreign('id_mitra')->references('id')->on('mitra')->onDelete('cascade'); 

            // Relasi ke Jabatan Mitra
            $table->string('kode_jabatan', 50)->nullable();
            $table->foreign('kode_jabatan')
                  ->references('kode_jabatan')->on('jabatan_mitra')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->integer('volume_tugas')->default(0);

            // Constraint: Satu mitra tidak boleh ganda di satu penugasan yang sama
            $table->unique(['id_penugasan', 'id_mitra'], 'unik_penugasan_mitra');

            $table->timestamp('created_at')->useCurrent();
            // Tabel ini di SQL Anda tidak memiliki updated_at, tapi boleh ditambahkan jika perlu
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_penugasan');
        Schema::dropIfExists('penugasan');
    }
};