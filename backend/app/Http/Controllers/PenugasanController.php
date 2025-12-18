<?php

namespace App\Http\Controllers;

use App\Models\Penugasan;
use App\Models\Perencanaan;
use App\Models\KelompokPenugasan;
use App\Models\KelompokPerencanaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            'id_pengawas'    => 'required|exists:user,id',
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
     * 3. IMPORT DARI PERENCANAAN
     * Menyalin data dari Perencanaan ke Penugasan.
     */
    public function importFromPerencanaan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids_perencanaan' => 'required|array', // Array ID Perencanaan yang mau diteruskan
            'ids_perencanaan.*' => 'exists:perencanaan,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $countHeader = 0;
            $countMembers = 0;

            // 1. Loop setiap ID Perencanaan yang dikirim
            foreach ($request->ids_perencanaan as $idPerencanaan) {

                // Ambil Data Perencanaan & Anggotanya (Gunakan 'kelompok' sesuai Model)
                $perencanaan = Perencanaan::with('kelompok')->find($idPerencanaan);

                if (!$perencanaan) continue;

                // 2. LOGIKA HEADER PENUGASAN (Upsert berdasarkan id_subkegiatan)
                $penugasan = Penugasan::updateOrCreate(
                    [
                        'id_subkegiatan' => $perencanaan->id_subkegiatan
                    ],
                    [
                        'id_pengawas' => $perencanaan->id_pengawas,
                        'updated_at'  => now()
                    ]
                );
                $countHeader++;

                // 3. LOGIKA ANGGOTA (KELOMPOK PENUGASAN)
                // Loop menggunakan properti 'kelompok' dari relasi di Model Perencanaan
                foreach ($perencanaan->kelompok as $anggotaPlan) {

                    // Upsert berdasarkan id_penugasan DAN id_mitra
                    KelompokPenugasan::updateOrCreate(
                        [
                            'id_penugasan' => $penugasan->id,
                            'id_mitra'     => $anggotaPlan->id_mitra,
                        ],
                        [
                            'kode_jabatan' => $anggotaPlan->kode_jabatan,
                            'volume_tugas' => $anggotaPlan->volume_tugas,
                            'created_at'   => now()
                        ]
                    );
                    $countMembers++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil meneruskan data.\n{$countHeader} Kegiatan diproses.\n{$countMembers} Anggota berhasil disinkronisasi."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal meneruskan data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 4. GET SINGLE PENUGASAN
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
     * 5. UPDATE PENUGASAN (STATUS & DATA LAIN)
     * Digunakan untuk mengubah status (menunggu/disetujui) atau mengedit data header.
     */
    public function update(Request $request, $id)
    {
        $penugasan = Penugasan::find($id);
        if (!$penugasan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'status_penugasan' => 'nullable|in:menunggu,disetujui',
            'id_subkegiatan'   => 'nullable|exists:subkegiatan,id',
            'id_pengawas'      => 'nullable|exists:user,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Update data (hanya field yang dikirim saja)
            $penugasan->update($request->only([
                'status_penugasan', 
                'id_subkegiatan', 
                'id_pengawas'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Penugasan berhasil diperbarui.',
                'data' => $this->formatItem($penugasan) // Return data terbaru dengan format lengkap
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 6. GET ANGGOTA BY PENUGASAN ID
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
                ->where('kp.id_penugasan', $id)
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

    /**
     * 7. GET BY MITRA AND PERIODE
     */
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

                // Join ke Honorarium
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
                    DB::raw("(kp.volume_tugas * IFNULL(h.tarif, 0)) as total_honor"),
                    DB::raw("COALESCE(h.beban_anggaran) as beban_anggaran")
                ])
                ->orderBy('s.tanggal_mulai', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $tasks
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 8. DELETE PENUGASAN
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

    private function formatSingle($id)
    {
        $item = Penugasan::with(['subkegiatan.kegiatan', 'pengawas'])->find($id);
        if (!$item) return null;

        return $this->formatItem($item);
    }

    private function formatItem($item)
    {
        return [
            'id_penugasan'         => $item->id,
            
            // --- TAMBAHAN: STATUS PENUGASAN ---
            'status_penugasan'     => $item->status_penugasan ?? 'menunggu', 
            
            'penugasan_created_at' => $item->created_at,

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