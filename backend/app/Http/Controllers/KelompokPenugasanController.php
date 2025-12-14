<?php

namespace App\Http\Controllers;

use App\Models\KelompokPenugasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelompokPenugasanController extends Controller
{
    // GET: Ambil semua data kelompok penugasan (Flattened untuk Frontend)
    public function index()
    {
        // Load relasi yang diperlukan
        // Pastikan Model KelompokPenugasan memiliki method: mitra(), penugasan(), jabatan()
        $data = KelompokPenugasan::with([
                    'mitra', 
                    'penugasan.subkegiatan.kegiatan', 
                    'penugasan.pengawas',
                    'jabatan' // Relasi ke JabatanMitra (jika ada di model)
                ])
                ->latest()
                ->get();

        // Transformasi data agar 'flat' sesuai kebutuhan Frontend React
        $formatted = $data->map(function($item) {
            return [
                'id_kelompok'       => $item->id,
                'id_penugasan'      => $item->id_penugasan,
                
                // DATA MITRA (PENTING: Frontend mencari 'nama_lengkap' atau 'nama_mitra')
                'id_mitra'          => $item->id_mitra,
                'nama_mitra'        => $item->mitra ? $item->mitra->nama_lengkap : 'Mitra Terhapus',
                'nama_lengkap'      => $item->mitra ? $item->mitra->nama_lengkap : 'Mitra Terhapus',
                'nik_mitra'         => $item->mitra ? $item->mitra->nik : '-',
                
                // Data Jabatan & Tugas
                'kode_jabatan'      => $item->kode_jabatan,
                'nama_jabatan'      => $item->jabatan ? $item->jabatan->nama_jabatan : ($item->kode_jabatan ?? '-'),
                'volume_tugas'      => $item->volume_tugas,
                
                // Data Parent (Info Kegiatan untuk konteks)
                'nama_sub_kegiatan' => $item->penugasan && $item->penugasan->subkegiatan ? $item->penugasan->subkegiatan->nama_sub_kegiatan : '-',
                'nama_kegiatan'     => $item->penugasan && $item->penugasan->subkegiatan && $item->penugasan->subkegiatan->kegiatan 
                                        ? $item->penugasan->subkegiatan->kegiatan->nama_kegiatan 
                                        : '-',
                'nama_pengawas'     => $item->penugasan && $item->penugasan->pengawas ? $item->penugasan->pengawas->username : '-',
            ];
        });

        return response()->json(['status' => 'success', 'data' => $formatted]);
    }

    // Update Kelompok Penugasan (Misal: Edit Volume / Jabatan)
    public function update(Request $request, $id)
    {
        $kp = KelompokPenugasan::find($id);
        if (!$kp) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $kp->update($request->only(['kode_jabatan', 'volume_tugas']));

        return response()->json(['status' => 'success', 'message' => 'Data berhasil diperbarui']);
    }

    // Hapus Mitra dari Penugasan
    public function destroy($id)
    {
        $kp = KelompokPenugasan::find($id);
        if (!$kp) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $kp->delete();

        return response()->json(['status' => 'success', 'message' => 'Mitra berhasil dihapus dari penugasan.']);
    }
}