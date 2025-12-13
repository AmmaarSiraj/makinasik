<?php

namespace App\Http\Controllers;

use App\Models\Penugasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenugasanController extends Controller
{
    // List semua penugasan
    public function index()
    {
        $data = Penugasan::with(['subkegiatan', 'pengawas', 'kelompok.mitra', 'kelompok.jabatan'])
                         ->latest()
                         ->get();
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    // Buat Penugasan Baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_subkegiatan' => 'required|exists:subkegiatan,id',
            'id_pengawas'    => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cek apakah subkegiatan ini sudah punya penugasan? (Opsional: 1 Sub = 1 Penugasan)
        // Jika 1 sub boleh banyak penugasan, hapus pengecekan ini.
        $exists = Penugasan::where('id_subkegiatan', $request->id_subkegiatan)->first();
        if ($exists) {
            return response()->json(['message' => 'Penugasan untuk subkegiatan ini sudah ada'], 409);
        }

        $penugasan = Penugasan::create([
            'id_subkegiatan' => $request->id_subkegiatan,
            'id_pengawas'    => $request->id_pengawas
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Penugasan berhasil dibuat',
            'data' => $penugasan
        ], 201);
    }

    public function show($id)
    {
        $penugasan = Penugasan::with(['subkegiatan', 'pengawas', 'kelompok.mitra', 'kelompok.jabatan'])->find($id);

        if (!$penugasan) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        return response()->json(['status' => 'success', 'data' => $penugasan]);
    }

    public function destroy($id)
    {
        $penugasan = Penugasan::find($id);
        if (!$penugasan) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $penugasan->delete(); // Kelompok penugasan otomatis terhapus (CASCADE)
        return response()->json(['status' => 'success', 'message' => 'Penugasan dihapus']);
    }
}