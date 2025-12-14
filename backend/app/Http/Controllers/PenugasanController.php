<?php

namespace App\Http\Controllers;

use App\Models\Penugasan;
use App\Models\KelompokPenugasan; // Pastikan Model ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Untuk perhitungan raw query honor

class PenugasanController extends Controller
{
    // 1. GET ALL PENUGASAN (Disesuaikan agar strukturnya datar/flat)
    public function index()
    {
        // Load relasi nested: subkegiatan -> kegiatan, dan pengawas
        $penugasan = Penugasan::with(['subkegiatan.kegiatan', 'pengawas'])
            ->latest()
            ->get();

        // Transformasi data agar sesuai dengan frontend (seperti Node.js)
        $formattedData = $penugasan->map(function ($item) {
            return [
                'id_penugasan'         => $item->id,
                'penugasan_created_at' => $item->created_at,

                // Data Subkegiatan
                'id_subkegiatan'       => $item->subkegiatan ? $item->subkegiatan->id : null,
                'nama_sub_kegiatan'    => $item->subkegiatan ? $item->subkegiatan->nama_sub_kegiatan : '-',
                'tanggal_mulai'        => $item->subkegiatan ? $item->subkegiatan->tanggal_mulai : null,
                'tanggal_selesai'      => $item->subkegiatan ? $item->subkegiatan->tanggal_selesai : null,
                
                // Data Kegiatan (Nested di dalam subkegiatan)
                'id_kegiatan'          => $item->subkegiatan && $item->subkegiatan->kegiatan ? $item->subkegiatan->kegiatan->id : null,
                'nama_kegiatan'        => $item->subkegiatan && $item->subkegiatan->kegiatan ? $item->subkegiatan->kegiatan->nama_kegiatan : '-',

                // Data Pengawas
                'id_pengawas'          => $item->pengawas ? $item->pengawas->id : null,
                'nama_pengawas'        => $item->pengawas ? $item->pengawas->username : '-', // Pastikan field username ada di tabel users
                'email_pengawas'       => $item->pengawas ? $item->pengawas->email : null,
                'role_pengawas'        => $item->pengawas ? $item->pengawas->role : null,
            ];
        });

        // Kembalikan dalam key 'data' (sesuai yang kita bahas sebelumnya)
        return response()->json(['status' => 'success', 'data' => $formattedData]);
    }

    // 2. CREATE PENUGASAN
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_subkegiatan' => 'required|exists:subkegiatan,id',
            'id_pengawas'    => 'required|exists:users,id',
            'anggota'        => 'nullable|array' // Validasi input anggota
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // A. Buat Header Penugasan
            $penugasan = Penugasan::create([
                'id_subkegiatan' => $request->id_subkegiatan,
                'id_pengawas'    => $request->id_pengawas
            ]);

            // B. Masukkan Anggota (Jika ada)
            if ($request->has('anggota') && is_array($request->anggota)) {
                foreach ($request->anggota as $anggota) {
                    $vol = isset($anggota['volume_tugas']) ? intval($anggota['volume_tugas']) : 0;

                    KelompokPenugasan::create([
                        'id_penugasan' => $penugasan->id,
                        'id_mitra'     => $anggota['id_mitra'],
                        'kode_jabatan' => $anggota['kode_jabatan'] ?? null,
                        'volume_tugas' => $vol
                    ]);
                }
            }

            DB::commit();

            // Ambil data yang baru dibuat dengan format lengkap (untuk response)
            // Kita panggil fungsi show() logic secara internal atau format manual
            return response()->json([
                'status' => 'success',
                'message' => 'Penugasan berhasil dibuat beserta anggota tim.',
                'data' => $this->formatSingle($penugasan->id)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal membuat penugasan: ' . $e->getMessage()], 500);
        }
    }

    // 3. GET SINGLE PENUGASAN (Detail)
    public function show($id)
    {
        $data = $this->formatSingle($id);
        if (!$data) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    // 4. GET ANGGOTA BY PENUGASAN ID (PENTING UNTUK Frontend toggleRow)
    // Route: GET /api/penugasan/{id}/anggota
    public function getAnggota($id)
    {
        try {
            // Menggunakan Raw Query atau Query Builder agar bisa join Honorarium & Hitung Total Honor
            // Logic ini meniru: (tarif * volume) as total_honor
            $anggota = DB::table('kelompok_penugasan as kp')
                ->join('mitra as m', 'kp.id_mitra', '=', 'm.id')
                ->join('penugasan as p', 'kp.id_penugasan', '=', 'p.id')
                ->leftJoin('jabatan_mitra as jm', 'kp.kode_jabatan', '=', 'jm.kode_jabatan')
                // Join Honorarium berdasarkan subkegiatan dan jabatan
                ->leftJoin('honorarium as h', function ($join) {
                    $join->on('h.id_subkegiatan', '=', 'p.id_subkegiatan')
                        ->on('h.kode_jabatan', '=', 'kp.kode_jabatan');
                })
                ->where('kp.id_penugasan', $id)
                ->select([
                    'm.id as id_mitra',
                    'm.nama_lengkap',
                    'm.nik',
                    'm.no_hp',
                    'kp.id as id_kelompok',
                    'kp.created_at as bergabung_sejak',
                    'kp.kode_jabatan',
                    'kp.volume_tugas',
                    DB::raw("IFNULL(jm.nama_jabatan, 'Belum ditentukan') as nama_jabatan"),
                    DB::raw("IFNULL(h.tarif, 0) as harga_satuan"),
                    DB::raw("(IFNULL(h.tarif, 0) * kp.volume_tugas) as total_honor")
                ])
                ->orderBy('m.nama_lengkap', 'asc')
                ->get();

            return response()->json($anggota); // Frontend mengharapkan array langsung untuk endpoint ini

        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // 5. DELETE PENUGASAN
    public function destroy($id)
    {
        $penugasan = Penugasan::find($id);
        if (!$penugasan) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $penugasan->delete(); // Kelompok penugasan otomatis terhapus (jika ada cascade di db)
        return response()->json(['status' => 'success', 'message' => 'Penugasan berhasil dihapus.']);
    }

    // --- HELPER FUNCTION ---
    private function formatSingle($id)
    {
        $item = Penugasan::with(['subkegiatan.kegiatan', 'pengawas'])->find($id);
        if (!$item) return null;

        return [
            'id_penugasan'         => $item->id,
            'penugasan_created_at' => $item->created_at,
            'id_subkegiatan'       => $item->subkegiatan ? $item->subkegiatan->id : null,
            'nama_sub_kegiatan'    => $item->subkegiatan ? $item->subkegiatan->nama_sub_kegiatan : '-',
            'tanggal_mulai'        => $item->subkegiatan ? $item->subkegiatan->tanggal_mulai : null,
            'tanggal_selesai'      => $item->subkegiatan ? $item->subkegiatan->tanggal_selesai : null,
            'id_kegiatan'          => $item->subkegiatan && $item->subkegiatan->kegiatan ? $item->subkegiatan->kegiatan->id : null,
            'nama_kegiatan'        => $item->subkegiatan && $item->subkegiatan->kegiatan ? $item->subkegiatan->kegiatan->nama_kegiatan : '-',
            'id_pengawas'          => $item->pengawas ? $item->pengawas->id : null,
            'nama_pengawas'        => $item->pengawas ? $item->pengawas->username : '-',
            'email_pengawas'       => $item->pengawas ? $item->pengawas->email : null,
            'role_pengawas'        => $item->pengawas ? $item->pengawas->role : null,
        ];
    }
}
