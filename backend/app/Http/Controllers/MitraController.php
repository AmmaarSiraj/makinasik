<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\TahunAktif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MitraController extends Controller
{
    /**
     * 1. GET ALL MITRA (OPTIMIZED)
     * Mengambil semua data mitra dan menyertakan riwayat tahun aktifnya.
     */
    public function index(Request $request)
    {
        $query = Mitra::query();

        // Fitur Pencarian Global
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('sobat_id', 'like', "%{$search}%");
            });
        }

        $mitra = $query->orderBy('nama_lengkap', 'asc')->get();

        // Transform data untuk menyertakan riwayat tahun aktif sebagai string
        $mitra->transform(function ($item) {
            $years = TahunAktif::where('user_id', $item->id)
                        ->orderBy('tahun', 'desc')
                        ->pluck('tahun')
                        ->toArray();
            
            // Menggunakan setAttribute agar properti ini ikut terekspos dalam JSON
            $item->setAttribute('riwayat_tahun', implode(', ', $years));
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $mitra
        ]);
    }
    
    /**
     * Endpoint khusus untuk kebutuhan tabel dengan pagination (Server-side processing)
     */
    public function optimize(Request $request)
    {
        $selectedYear = $request->query('year', date('Y'));
        $search = $request->search;

        $query = Mitra::query();

        // Filter hanya yang aktif di tahun terpilih
        $query->whereHas('tahunAktif', function($q) use ($selectedYear) {
            $q->where('tahun', $selectedYear);
        });

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('sobat_id', 'like', "%{$search}%");
            });
        }

        // Hitung total aktif untuk meta data
        $totalActiveInYear = Mitra::whereHas('tahunAktif', function($q) use ($selectedYear) {
            $q->where('tahun', $selectedYear);
        })->count();

        $mitra = $query->with(['tahunAktif' => function ($q) {
            $q->orderBy('tahun', 'desc');
        }])
        ->orderBy('nama_lengkap', 'asc')
        ->paginate(20);

        $mitra->getCollection()->transform(function ($item) {
            $item->setAttribute('riwayat_tahun', $item->tahunAktif->pluck('tahun')->implode(', '));
            unset($item->tahunAktif); // Bersihkan relasi agar response lebih ringan
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Data mitra berhasil diambil',
            'data' => $mitra,
            'extra_meta' => [
                'selected_year' => $selectedYear,
                'total_active_count' => $totalActiveInYear
            ]
        ], 200);
    }

    /**
     * Mengambil mitra berdasarkan periode penugasan (berguna untuk filter laporan)
     */
    public function getByPeriode($periode)
    {
        try {
            // Periode format: "2025-12"
            $parts = explode('-', $periode);
            if (count($parts) !== 2) {
                return response()->json(['message' => 'Format periode salah'], 400);
            }
            $year = $parts[0];
            $month = $parts[1];

            // Query menggunakan JOIN agar lebih performa dan akurat
            $mitra = Mitra::select('mitra.*')
                ->join('kelompok_penugasan', 'mitra.id', '=', 'kelompok_penugasan.id_mitra')
                ->join('penugasan', 'kelompok_penugasan.id_penugasan', '=', 'penugasan.id')
                ->join('subkegiatan', 'penugasan.id_subkegiatan', '=', 'subkegiatan.id')
                // Filter berdasarkan tanggal mulai kegiatan
                ->whereYear('subkegiatan.tanggal_mulai', $year)
                ->whereMonth('subkegiatan.tanggal_mulai', $month)
                ->distinct() // Mencegah duplikasi jika mitra punya banyak tugas di bulan itu
                ->orderBy('mitra.nama_lengkap', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $mitra
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * 2. TAMBAH MITRA MANUAL
     */
  public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nik'          => 'required|string|max:50',
            'nomor_hp'     => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $targetYear = $request->input('tahun_daftar', date('Y'));

        DB::beginTransaction();
        try {
            $mitra = Mitra::updateOrCreate(
                ['nik' => $request->nik],
                [
                    'nama_lengkap' => $request->nama_lengkap,
                    'sobat_id'     => $request->sobat_id,
                    'alamat'       => $request->alamat,
                    'jenis_kelamin'=> $request->jenis_kelamin,
                    'pendidikan'   => $request->pendidikan,
                    'pekerjaan'    => $request->pekerjaan,
                    'deskripsi_pekerjaan_lain' => $request->deskripsi_pekerjaan_lain,
                    'nomor_hp'     => $request->nomor_hp,
                    'email'        => $request->email,
                ]
            );

            $isActive = TahunAktif::where('user_id', $mitra->id)
                                  ->where('tahun', $targetYear)
                                  ->exists();

            if (!$isActive) {
                TahunAktif::create([
                    'user_id' => $mitra->id,
                    'tahun'   => $targetYear,
                    'status'  => 'aktif'
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Mitra berhasil disimpan dan diaktifkan.',
                'data' => $mitra
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}