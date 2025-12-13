<?php

namespace App\Http\Controllers;

use App\Models\Honorarium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HonorariumController extends Controller
{
    public function index()
    {
        // Mengambil honor lengkap dengan nama jabatan dan nama subkegiatan
        $honor = Honorarium::with(['jabatan', 'subkegiatan'])->latest()->get();
        return response()->json(['status' => 'success', 'data' => $honor]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_subkegiatan' => 'required|exists:subkegiatan,id',
            'kode_jabatan'   => 'required|exists:jabatan_mitra,kode_jabatan',
            'tarif'          => 'required|numeric|min:0',
            'id_satuan'      => 'required|integer',
            'basis_volume'   => 'required|integer|min:1',
            'beban_anggaran' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $honor = Honorarium::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Honorarium berhasil ditambahkan',
            'data' => $honor
        ], 201);
    }

    public function show($id)
    {
        $honor = Honorarium::with(['jabatan', 'subkegiatan'])->find($id);

        if (!$honor) {
            return response()->json(['message' => 'Data Honorarium tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $honor]);
    }

    public function update(Request $request, $id)
    {
        $honor = Honorarium::find($id);

        if (!$honor) {
            return response()->json(['message' => 'Data Honorarium tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_subkegiatan' => 'sometimes|exists:subkegiatan,id',
            'kode_jabatan'   => 'sometimes|exists:jabatan_mitra,kode_jabatan',
            'tarif'          => 'sometimes|numeric|min:0',
            'id_satuan'      => 'sometimes|integer',
            'basis_volume'   => 'sometimes|integer|min:1',
            'beban_anggaran' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $honor->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Honorarium berhasil diperbarui',
            'data' => $honor
        ]);
    }

    public function destroy($id)
    {
        $honor = Honorarium::find($id);

        if (!$honor) {
            return response()->json(['message' => 'Data Honorarium tidak ditemukan'], 404);
        }

        $honor->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Honorarium berhasil dihapus'
        ]);
    }

    /**
     * Fitur Tambahan: Mengambil semua honor berdasarkan ID Subkegiatan tertentu
     * Route: GET /api/subkegiatan/{id}/honorarium
     */
    public function getBySubkegiatan($idSubkegiatan)
    {
        $honor = Honorarium::where('id_subkegiatan', $idSubkegiatan)
                           ->with('jabatan') // Sertakan info jabatan
                           ->get();

        return response()->json([
            'status' => 'success',
            'data' => $honor
        ]);
    }
}