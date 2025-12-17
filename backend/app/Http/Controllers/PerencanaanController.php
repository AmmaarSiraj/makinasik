<?php

namespace App\Http\Controllers;

use App\Models\Perencanaan;
use App\Models\KelompokPerencanaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerencanaanController extends Controller
{
    /**
     * 1. GET ALL PERENCANAAN
     * Mengembalikan data dalam format flat (datar) dengan tanggal yang sudah diformat bersih.
     */
    public function index()
    {
        // Load relasi nested: subkegiatan -> kegiatan, dan pengawas
        $perencanaan = Perencanaan::with(['subkegiatan.kegiatan', 'pengawas'])
            ->latest()
            ->get();

        // Transformasi data menggunakan helper formatItem
        $formattedData = $perencanaan->map(function ($item) {
            return $this->formatItem($item);
        });

        return response()->json(['status' => 'success', 'data' => $formattedData]);
    }

    /**
     * 2. CREATE PERENCANAAN
     * Membuat perencanaan baru beserta anggota tim (jika ada) dalam satu transaksi.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_subkegiatan' => 'required|exists:subkegiatan,id',
            'id_pengawas'    => 'required|exists:user,id',
            'anggota'        => 'nullable|array',
            'anggota.*.id_mitra'     => 'required|exists:mitra,id',
            'anggota.*.kode_jabatan' => 'required|exists:jabatan_mitra,kode_jabatan',
            'anggota.*.volume_tugas' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cek duplikasi: 1 Subkegiatan sebaiknya hanya punya 1 Perencanaan
        $exists = Perencanaan::where('id_subkegiatan', $request->id_subkegiatan)->first();
        if ($exists) {
            return response()->json(['message' => 'Perencanaan untuk subkegiatan ini sudah ada'], 409);
        }

        DB::beginTransaction();
        try {
            // A. Buat Header Perencanaan
            $perencanaan = Perencanaan::create([
                'id_subkegiatan' => $request->id_subkegiatan,
                'id_pengawas'    => $request->id_pengawas
            ]);

            // B. Masukkan Anggota (Jika ada)
            if ($request->has('anggota') && is_array($request->anggota)) {
                foreach ($request->anggota as $anggota) {
                    KelompokPerencanaan::create([
                        'id_perencanaan' => $perencanaan->id,
                        'id_mitra'       => $anggota['id_mitra'],
                        'kode_jabatan'   => $anggota['kode_jabatan'] ?? null,
                        'volume_tugas'   => $anggota['volume_tugas'] ?? 0
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Perencanaan berhasil dibuat beserta anggota tim.',
                'data' => $this->formatSingle($perencanaan->id)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal membuat perencanaan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 3. GET SINGLE PERENCANAAN
     */
    public function show($id)
    {
        $data = $this->formatSingle($id);
        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * 4. GET ANGGOTA BY PERENCANAAN ID
     * Mengambil daftar anggota tim perencanaan melalui join manual untuk performa.
     */
    public function getAnggota($id)
    {
        try {
            $anggota = DB::table('kelompok_perencanaan as kp')
                ->join('mitra as m', 'kp.id_mitra', '=', 'm.id')
                ->join('perencanaan as p', 'kp.id_perencanaan', '=', 'p.id')
                ->leftJoin('jabatan_mitra as jm', 'kp.kode_jabatan', '=', 'jm.kode_jabatan')
                // Join ke tabel honorarium untuk mengambil tarif berdasarkan subkegiatan dan jabatan
                ->leftJoin('honorarium as h', function ($join) {
                    $join->on('h.id_subkegiatan', '=', 'p.id_subkegiatan')
                        ->on('h.kode_jabatan', '=', 'kp.kode_jabatan');
                })
                ->where('kp.id_perencanaan', $id) // Filter berdasarkan ID Perencanaan
                ->select([
                    'm.id as id_mitra',
                    'm.nama_lengkap',
                    'm.nik',
                    'm.nomor_hp',
                    'kp.id as id_kelompok',
                    'kp.created_at as bergabung_sejak',
                    'kp.kode_jabatan',
                    'kp.volume_tugas',
                    DB::raw("IFNULL(jm.nama_jabatan, 'Belum ditentukan') as nama_jabatan"),
                    DB::raw("IFNULL(h.tarif, 0) as harga_satuan"),
                    // Menghitung total honor: volume perencanaan * tarif honorarium
                    DB::raw("(IFNULL(h.tarif, 0) * kp.volume_tugas) as total_honor")
                ])
                ->orderBy('m.nama_lengkap', 'asc')
                ->get();

            return response()->json($anggota);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getByMitraAndPeriode($id_mitra, $periode)
    {
        try {
            // Periode format: "2025-12"
            $parts = explode('-', $periode);
            if (count($parts) !== 2) {
                return response()->json(['message' => 'Format periode salah. Gunakan YYYY-MM'], 400);
            }
            $year = $parts[0];
            $month = $parts[1];

            // Query Kompleks untuk Perencanaan
            $plans = DB::table('kelompok_perencanaan as kp')
                ->join('perencanaan as p', 'kp.id_perencanaan', '=', 'p.id')
                ->join('subkegiatan as s', 'p.id_subkegiatan', '=', 's.id')
                ->join('kegiatan as k', 's.id_kegiatan', '=', 'k.id')
                ->leftJoin('jabatan_mitra as jm', 'kp.kode_jabatan', '=', 'jm.kode_jabatan')
                
                // Join ke Honorarium untuk estimasi anggaran/tarif
                ->leftJoin('honorarium as h', function ($join) {
                    $join->on('h.id_subkegiatan', '=', 's.id')
                         ->on('h.kode_jabatan', '=', 'kp.kode_jabatan');
                })

                // Join Satuan Kegiatan lewat Honorarium
                ->leftJoin('satuan_kegiatan as sat', 'h.id_satuan', '=', 'sat.id')

                ->where('kp.id_mitra', $id_mitra)
                ->whereYear('s.tanggal_mulai', $year)
                ->whereMonth('s.tanggal_mulai', $month)
                ->select([
                    's.nama_sub_kegiatan',
                    's.tanggal_mulai',
                    's.tanggal_selesai',
                    'kp.volume_tugas as target_volume',
                    'sat.nama_satuan',
                    'jm.nama_jabatan',
                    DB::raw("IFNULL(h.tarif, 0) as harga_satuan"),
                    DB::raw("(kp.volume_tugas * IFNULL(h.tarif, 0)) as total_honor_estimasi"),
                    DB::raw("COALESCE(h.beban_anggaran, k.nama_kegiatan) as beban_anggaran") 
                ])
                ->orderBy('s.tanggal_mulai', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $plans
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 5. DELETE PERENCANAAN
     */
    public function destroy($id)
    {
        $perencanaan = Perencanaan::find($id);
        if (!$perencanaan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $perencanaan->delete();
        return response()->json(['status' => 'success', 'message' => 'Perencanaan berhasil dihapus.']);
    }

    // =========================================================================
    // HELPER FUNCTIONS
    // =========================================================================

    /**
     * Helper untuk mengambil data single by ID dan memformatnya.
     */
    private function formatSingle($id)
    {
        $item = Perencanaan::with(['subkegiatan.kegiatan', 'pengawas'])->find($id);
        if (!$item) return null;

        return $this->formatItem($item);
    }

    /**
     * Helper utama untuk memformat output JSON agar tanggal bersih (Y-m-d).
     */
    private function formatItem($item)
    {
        return [
            'id_perencanaan'         => $item->id,
            'perencanaan_created_at' => $item->created_at,

            // Data Subkegiatan
            'id_subkegiatan'       => $item->subkegiatan ? $item->subkegiatan->id : null,
            'nama_sub_kegiatan'    => $item->subkegiatan ? $item->subkegiatan->nama_sub_kegiatan : '-',
            
            // Format Tanggal Bersih YYYY-MM-DD menggunakan Carbon
            'tanggal_mulai'        => $item->subkegiatan && $item->subkegiatan->tanggal_mulai 
                                      ? Carbon::parse($item->subkegiatan->tanggal_mulai)->format('Y-m-d')
                                      : null,
            'tanggal_selesai'      => $item->subkegiatan && $item->subkegiatan->tanggal_selesai 
                                      ? Carbon::parse($item->subkegiatan->tanggal_selesai)->format('Y-m-d')
                                      : null,
            
            // Data Kegiatan (Induk)
            'id_kegiatan'          => $item->subkegiatan && $item->subkegiatan->kegiatan ? $item->subkegiatan->kegiatan->id : null,
            'nama_kegiatan'        => $item->subkegiatan && $item->subkegiatan->kegiatan ? $item->subkegiatan->kegiatan->nama_kegiatan : '-',

            // Data Pengawas
            'id_pengawas'          => $item->pengawas ? $item->pengawas->id : null,
            'nama_pengawas'        => $item->pengawas ? $item->pengawas->username : '-',
            'email_pengawas'       => $item->pengawas ? $item->pengawas->email : null,
            'role_pengawas'        => $item->pengawas ? $item->pengawas->role : null,
        ];
    }
}