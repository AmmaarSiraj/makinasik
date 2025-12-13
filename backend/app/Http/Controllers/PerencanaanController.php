<?php

namespace App\Http\Controllers;

use App\Models\Perencanaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerencanaanController extends Controller
{
    // List semua perencanaan
    public function index()
    {
        $data = Perencanaan::with(['subkegiatan', 'pengawas', 'kelompok.mitra', 'kelompok.jabatan'])
                           ->latest()
                           ->get();
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    // Buat Perencanaan Baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_subkegiatan' => 'required|exists:subkegiatan,id',
            'id_pengawas'    => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cek duplikasi: 1 Subkegiatan sebaiknya hanya punya 1 Perencanaan
        $exists = Perencanaan::where('id_subkegiatan', $request->id_subkegiatan)->first();
        if ($exists) {
            return response()->json(['message' => 'Perencanaan untuk subkegiatan ini sudah ada'], 409);
        }

        $perencanaan = Perencanaan::create([
            'id_subkegiatan' => $request->id_subkegiatan,
            'id_pengawas'    => $request->id_pengawas
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Perencanaan berhasil dibuat',
            'data' => $perencanaan
        ], 201);
    }

    public function show($id)
    {
        $perencanaan = Perencanaan::with(['subkegiatan', 'pengawas', 'kelompok.mitra', 'kelompok.jabatan'])->find($id);

        if (!$perencanaan) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        return response()->json(['status' => 'success', 'data' => $perencanaan]);
    }

    public function destroy($id)
    {
        $perencanaan = Perencanaan::find($id);
        if (!$perencanaan) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $perencanaan->delete(); // Kelompok perencanaan otomatis terhapus (CASCADE)
        return response()->json(['status' => 'success', 'message' => 'Perencanaan dihapus']);
    }
}