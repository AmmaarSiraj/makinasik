<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MitraController extends Controller
{
    /**
     * Tampilkan semua data mitra
     */
    public function index(Request $request)
    {
        // Bisa tambah fitur pencarian by Nama/NIK
        $query = Mitra::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('sobat_id', 'like', "%{$search}%");
        }

        $data = $query->orderBy('nama_lengkap', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Tambah Mitra Baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nik'          => 'required|string|max:50|unique:mitra,nik',
            'sobat_id'     => 'nullable|string|max:50',
            'email'        => 'nullable|email|max:100',
            'nomor_hp'     => 'nullable|string|max:20',
            'jenis_kelamin'=> 'nullable|in:L,P',
            // Field lain opsional, tidak perlu validasi ketat
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mitra = Mitra::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Mitra berhasil ditambahkan',
            'data' => $mitra
        ], 201);
    }

    /**
     * Detail Mitra
     */
    public function show($id)
    {
        $mitra = Mitra::find($id);

        if (!$mitra) {
            return response()->json(['message' => 'Mitra tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $mitra]);
    }

    /**
     * Update Mitra
     */
    public function update(Request $request, $id)
    {
        $mitra = Mitra::find($id);

        if (!$mitra) {
            return response()->json(['message' => 'Mitra tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            // Unique tapi abaikan ID diri sendiri
            'nik'          => 'required|string|max:50|unique:mitra,nik,'.$id,
            'sobat_id'     => 'nullable|string|max:50',
            'email'        => 'nullable|email|max:100',
            'nomor_hp'     => 'nullable|string|max:20',
            'jenis_kelamin'=> 'nullable|in:L,P',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mitra->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Data mitra berhasil diperbarui',
            'data' => $mitra
        ]);
    }

    /**
     * Hapus Mitra
     */
    public function destroy($id)
    {
        $mitra = Mitra::find($id);

        if (!$mitra) {
            return response()->json(['message' => 'Mitra tidak ditemukan'], 404);
        }

        // Hati-hati: Jika ada relasi CASCADE di database, 
        // menghapus mitra akan menghapus dia dari semua kelompok_penugasan
        $mitra->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Mitra berhasil dihapus'
        ]);
    }
}