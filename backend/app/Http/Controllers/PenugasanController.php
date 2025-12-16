<?php

namespace App\Http\Controllers;

use App\Models\Penugasan;
use App\Models\KelompokPenugasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Penting: Import Carbon untuk format tanggal

class PenugasanController extends Controller
{
    /**
     * 1. GET ALL PENUGASAN
     * Mengembalikan data dalam format flat (datar) dengan tanggal yang sudah diformat bersih.
     */
    public function index()
    {
        // Load relasi nested: subkegiatan -> kegiatan, dan pengawas
        $penugasan = Penugasan::with(['subkegiatan.kegiatan', 'pengawas'])
            ->latest()
            ->get();

        // Transformasi data
        $formattedData = $penugasan->map(function ($item) {
            return $this->formatItem($item);
        });

        return response()->json(['status' => 'success', 'data' => $formattedData]);
    }

    /**
     * 2. CREATE PENUGASAN
     * Membuat penugasan baru beserta anggota tim (jika ada).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_subkegiatan' => 'required|exists:subkegiatan,id',
            'id_pengawas'    => 'required|exists:user,id', // <--- PERBAIKAN: Mengubah 'users' menjadi 'user'
            'anggota'        => 'nullable|array',
            'anggota.*.id_mitra'     => 'required|exists:mitra,id',
            'anggota.*.kode_jabatan' => 'required|exists:jabatan_mitra,kode_jabatan',
            'anggota.*.volume_tugas' => 'required|integer|min:1',
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

            // Return data yang baru dibuat dengan format lengkap
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

    /**
     * 3. GET SINGLE PENUGASAN
     * Detail satu penugasan.
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
     * 4. GET ANGGOTA BY PENUGASAN ID
     * Mengambil daftar anggota tim + hitungan honor.
     * Route: GET /api/penugasan/{id}/anggota
     */
    public function getAnggota($id)
    {
        try {
            $anggota = DB::table('kelompok_penugasan as kp')
                ->join('mitra as m', 'kp.id_mitra', '=', 'm.id')
                ->join('penugasan as p', 'kp.id_penugasan', '=', 'p.id')
                ->leftJoin('jabatan_mitra as jm', 'kp.kode_jabatan', '=', 'jm.kode_jabatan')
                // Join ke tabel honorarium untuk ambil tarif
                ->leftJoin('honorarium as h', function ($join) {
                    $join->on('h.id_subkegiatan', '=', 'p.id_subkegiatan')
                        ->on('h.kode_jabatan', '=', 'kp.kode_jabatan');
                })
                ->where('kp.id_penugasan', $id) // Filter utama berdasarkan ID Penugasan
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
                return response()->json(['message' => 'Format periode salah'], 400);
            }
            $year = $parts[0];
            $month = $parts[1];

            // Query Kompleks
            $tasks = DB::table('kelompok_penugasan as kp')
                ->join('penugasan as p', 'kp.id_penugasan', '=', 'p.id')
                ->join('subkegiatan as s', 'p.id_subkegiatan', '=', 's.id')
                ->join('kegiatan as k', 's.id_kegiatan', '=', 'k.id')
                ->leftJoin('jabatan_mitra as jm', 'kp.kode_jabatan', '=', 'jm.kode_jabatan')
                
                // 1. Join ke Honorarium dulu (karena id_satuan ada di sini)
                ->leftJoin('honorarium as h', function ($join) {
                    $join->on('h.id_subkegiatan', '=', 's.id')
                         ->on('h.kode_jabatan', '=', 'kp.kode_jabatan');
                })

                // 2. Join Satuan Kegiatan lewat Honorarium (BUKAN lewat subkegiatan)
                ->leftJoin('satuan_kegiatan as sat', 'h.id_satuan', '=', 'sat.id')

                ->where('kp.id_mitra', $id_mitra)
                ->whereYear('s.tanggal_mulai', $year)
                ->whereMonth('s.tanggal_mulai', $month)
                ->select([
                    's.nama_sub_kegiatan',
                    's.tanggal_mulai',
                    's.tanggal_selesai',
                    'kp.volume_tugas as target_volume',
                    'sat.nama_satuan', // Ambil dari tabel satuan yang di-join lewat honorarium
                    'jm.nama_jabatan',
                    // Ambil tarif, jika null anggap 0
                    DB::raw("IFNULL(h.tarif, 0) as harga_satuan"),
                    // Hitung total: volume * tarif
                    DB::raw("(kp.volume_tugas * IFNULL(h.tarif, 0)) as total_honor"),
                    // Beban anggaran: Prioritaskan dari honorarium, jika kosong ambil nama kegiatan
                    DB::raw("COALESCE(h.beban_anggaran) as beban_anggaran") 
                ])
                ->orderBy('s.tanggal_mulai', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $tasks
            ]);

        } catch (\Exception $e) {
            // Tampilkan pesan error detail untuk debugging
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 5. DELETE PENUGASAN
     */
    public function destroy($id)
    {
        $penugasan = Penugasan::find($id);
        if (!$penugasan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $penugasan->delete(); 
        return response()->json(['status' => 'success', 'message' => 'Penugasan berhasil dihapus.']);
    }

    // =========================================================================
    // HELPER FUNCTIONS
    // =========================================================================

    /**
     * Helper untuk mengambil data single by ID dan memformatnya.
     */
    private function formatSingle($id)
    {
        $item = Penugasan::with(['subkegiatan.kegiatan', 'pengawas'])->find($id);
        if (!$item) return null;

        return $this->formatItem($item);
    }

    /**
     * Helper utama untuk memformat output JSON agar tanggal bersih (Y-m-d).
     */
    private function formatItem($item)
    {
        return [
            'id_penugasan'         => $item->id,
            'penugasan_created_at' => $item->created_at, // Timestamp default oke untuk created_at

            // Data Subkegiatan
            'id_subkegiatan'       => $item->subkegiatan ? $item->subkegiatan->id : null,
            'nama_sub_kegiatan'    => $item->subkegiatan ? $item->subkegiatan->nama_sub_kegiatan : '-',
            
            // Format Tanggal Bersih YYYY-MM-DD
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