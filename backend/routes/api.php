<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KegiatanController;    // <--- Import ini
use App\Http\Controllers\SubkegiatanController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'Backend Laravel Berhasil Terhubung!']);
});

Route::post('/login', [AuthController::class, 'login']);


// --- PROTECTED ROUTES (Harus login / punya Token) ---
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [UserController::class, 'me']); // Cek profile sendiri

    // Manajemen User
    Route::get('/users', [UserController::class, 'index']); // Ambil semua list user

    Route::get('/kegiatan', [KegiatanController::class, 'index']);      // List semua
    Route::post('/kegiatan', [KegiatanController::class, 'store']);     // Buat baru
    Route::get('/kegiatan/{id}', [KegiatanController::class, 'show']);  // Detail
    Route::put('/kegiatan/{id}', [KegiatanController::class, 'update']); // Edit
    Route::delete('/kegiatan/{id}', [KegiatanController::class, 'destroy']); // Hapus

    // --- MANAJEMEN SUB KEGIATAN ---
    // Create (Butuh id_kegiatan di body)
    Route::post('/subkegiatan', [SubkegiatanController::class, 'store']); 

    Route::put('/subkegiatan/{id}', [SubkegiatanController::class, 'update']);
    Route::delete('/subkegiatan/{id}', [SubkegiatanController::class, 'destroy']);
    
    // Nanti bisa tambah route lain di sini (create, update, delete)
});