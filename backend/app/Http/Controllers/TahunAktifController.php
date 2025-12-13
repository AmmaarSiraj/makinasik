<?php

namespace App\Http\Controllers;

use App\Models\TahunAktif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TahunAktifController extends Controller
{
    /**
     * Tampilkan semua data tahun aktif (bisa difilter per user jika perlu)
     */
    public function index(Request $request)
    {
        $query = TahunAktif::with('user');

        // Jika ada filter user_id dari query param (?user_id=1)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $data = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Simpan data tahun aktif baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'tahun'   => 'required|digits:4', // Harus 4 angka (misal: 2025)
            'status'  => 'nullable|in:aktif,non-aktif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cek apakah kombinasi user dan tahun sudah ada? (Opsional, agar tidak duplikat)
        $exists = TahunAktif::where('user_id', $request->user_id)
                            ->where('tahun', $request->tahun)
                            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Tahun ini sudah terdaftar untuk user tersebut'], 409);
        }

        $tahunAktif = TahunAktif::create([
            'user_id' => $request->user_id,
            'tahun'   => $request->tahun,
            'status'  => $request->status ?? 'aktif',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tahun aktif berhasil ditambahkan',
            'data' => $tahunAktif
        ], 201);
    }

    /**
     * Tampilkan detail
     */
    public function show($id)
    {
        $tahunAktif = TahunAktif::with('user')->find($id);

        if (!$tahunAktif) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $tahunAktif]);
    }

    /**
     * Update data
     */
    public function update(Request $request, $id)
    {
        $tahunAktif = TahunAktif::find($id);

        if (!$tahunAktif) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tahun'   => 'sometimes|digits:4',
            'status'  => 'sometimes|in:aktif,non-aktif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tahunAktif->update($request->only(['tahun', 'status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui',
            'data' => $tahunAktif
        ]);
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $tahunAktif = TahunAktif::find($id);

        if (!$tahunAktif) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $tahunAktif->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus'
        ]);
    }
}