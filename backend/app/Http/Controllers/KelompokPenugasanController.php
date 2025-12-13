<?php

namespace App\Http\Controllers;

use App\Models\KelompokPenugasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KelompokPenugasanController extends Controller
{
    public function index()
    {
        $data = KelompokPenugasan::with(['mitra', 'jabatan', 'penugasan'])->latest()->get();
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_penugasan' => 'required|exists:penugasan,id',
            'id_mitra'     => 'required|exists:mitra,id',
            'kode_jabatan' => 'required|exists:jabatan_mitra,kode_jabatan',
            'volume_tugas' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exists = KelompokPenugasan::where('id_penugasan', $request->id_penugasan)
                                   ->where('id_mitra', $request->id_mitra)
                                   ->exists();
        
        if ($exists) {
            return response()->json(['message' => 'Mitra ini sudah ada di dalam tim penugasan tersebut'], 422);
        }

        $anggota = KelompokPenugasan::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Mitra berhasil ditambahkan ke penugasan',
            'data' => $anggota
        ], 201);
    }

    public function show($id)
    {
        $data = KelompokPenugasan::with(['mitra', 'jabatan', 'penugasan'])->find($id);

        if (!$data) {
            return response()->json(['message' => 'Data anggota tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $anggota = KelompokPenugasan::find($id);
        if (!$anggota) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'kode_jabatan' => 'sometimes|exists:jabatan_mitra,kode_jabatan',
            'volume_tugas' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $anggota->update($request->only(['kode_jabatan', 'volume_tugas']));

        return response()->json(['status' => 'success', 'message' => 'Data anggota diperbarui', 'data' => $anggota]);
    }

    public function destroy($id)
    {
        $anggota = KelompokPenugasan::find($id);
        if (!$anggota) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $anggota->delete();
        return response()->json(['status' => 'success', 'message' => 'Mitra dihapus dari penugasan']);
    }

    public function getByPenugasan($idPenugasan)
    {
        $data = KelompokPenugasan::where('id_penugasan', $idPenugasan)
                                 ->with(['mitra', 'jabatan'])
                                 ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }
}