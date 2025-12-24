<?php

namespace App\Http\Controllers;

use App\Models\Subkegiatan;
use App\Models\Kegiatan;
use App\Models\Honorarium;
use App\Models\JabatanMitra;
use App\Models\SatuanKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class SubkegiatanController extends Controller
{
    public function index()
    {
        $subkegiatan = Subkegiatan::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $subkegiatan
        ]);
    }

    public function getByKegiatan($id_kegiatan)
    {
        $subs = Subkegiatan::where('id_kegiatan', $id_kegiatan)->get();
        return response()->json($subs);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kegiatan'       => 'required|exists:kegiatan,id',
            'nama_sub_kegiatan' => 'required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'tanggal_mulai'     => 'nullable|date',
            'tanggal_selesai'   => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'            => 'nullable|string|in:pending,progress,done',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sub = Subkegiatan::create([
            'id_kegiatan'       => $request->id_kegiatan,
            'nama_sub_kegiatan' => $request->nama_sub_kegiatan,
            'deskripsi'         => $request->deskripsi,
            'tanggal_mulai'     => $request->tanggal_mulai,
            'tanggal_selesai'   => $request->tanggal_selesai,
            'status'            => $request->status ?? 'pending',
        ]);

        $sub = Subkegiatan::where('created_at', $sub->created_at)
            ->where('nama_sub_kegiatan', $sub->nama_sub_kegiatan)
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Kegiatan berhasil ditambahkan',
            'data' => $sub
        ], 201);
    }

    public function show($id)
    {
        $sub = Subkegiatan::where('id', $id)->first();

        if (!$sub) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sub Kegiatan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $sub
        ]);
    }

    public function update(Request $request, $id)
    {
        $sub = Subkegiatan::find($id);

        if (!$sub) {
            return response()->json(['message' => 'Sub Kegiatan tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_sub_kegiatan' => 'required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'tanggal_mulai'     => 'nullable|date',
            'tanggal_selesai'   => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sub->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Kegiatan berhasil diupdate',
            'data' => $sub
        ]);
    }

    public function destroy($id)
    {
        $sub = Subkegiatan::find($id);

        if (!$sub) {
            return response()->json(['message' => 'Sub Kegiatan tidak ditemukan'], 404);
        }

        $sub->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Kegiatan berhasil dihapus'
        ]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'nama_kegiatan',
            'nama_sub_kegiatan',
            'deskripsi',
            'tanggal_mulai',
            'tanggal_selesai',
            'jabatan',
            'tarif',
            'total_dokumen',
            'satuan'
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $exampleData = [
            'Sensus Penduduk 2030',
            'Petugas Pemeriksaan Lapangan (PML)',
            'Rapat di Aula Utama',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+5 days')),
            'Petugas Pendataan',
            '150000',
            '10',
            'DOK'
        ];
        $sheet->fromArray($exampleData, null, 'A2');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, 'template_import_kegiatan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $file = $request->file('file');
        $successCount = 0;
        $errors = [];
    
        DB::beginTransaction();
    
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
    
            $headerRaw = array_shift($rows);
            if (!$headerRaw) throw new \Exception("File kosong atau format header salah.");
            $header = array_map(function ($h) { return trim(strtolower($h)); }, $headerRaw);
    
            $colMap = [
                'kegiatan'       => $this->findHeaderIndex($header, ['nama_kegiatan', 'kegiatan']),
                'subkegiatan'    => $this->findHeaderIndex($header, ['nama_sub_kegiatan', 'subkegiatan']),
                'deskripsi'      => $this->findHeaderIndex($header, ['deskripsi']),
                'tgl_mulai'      => $this->findHeaderIndex($header, ['tanggal_mulai', 'tgl_mulai']),
                'tgl_selesai'    => $this->findHeaderIndex($header, ['tanggal_selesai', 'tgl_selesai']),
                'jabatan'        => $this->findHeaderIndex($header, ['jabatan', 'nama_jabatan', 'kode_jabatan']),
                'tarif'          => $this->findHeaderIndex($header, ['tarif', 'harga_satuan']),
                'satuan'         => $this->findHeaderIndex($header, ['satuan', 'id_satuan']),
                'basis_volume'   => $this->findHeaderIndex($header, ['basis_volume', 'basis', 'total_dokumen']),
                'beban_anggaran' => $this->findHeaderIndex($header, ['beban_anggaran', 'beban']),
            ];
    
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $namaKegiatan = $this->getValue($row, $colMap['kegiatan']);
                $namaSub      = $this->getValue($row, $colMap['subkegiatan']);
    
                if (empty($namaKegiatan) || empty($namaSub)) continue;
    
                try {
                    $kegiatan = Kegiatan::firstOrCreate(
                        ['nama_kegiatan' => trim($namaKegiatan)],
                        ['deskripsi' => 'Auto-generated via import']
                    );
    
                    $sub = Subkegiatan::updateOrCreate(
                        [
                            'id_kegiatan'       => $kegiatan->id,
                            'nama_sub_kegiatan' => trim($namaSub)
                        ],
                        [
                            'deskripsi'       => $this->getValue($row, $colMap['deskripsi']),
                            'tanggal_mulai'   => $this->formatDate($this->getValue($row, $colMap['tgl_mulai'])),
                            'tanggal_selesai' => $this->formatDate($this->getValue($row, $colMap['tgl_selesai'])),
                            'status'          => 'aktif'
                        ]
                    );
    
                    $namaJabatan = $this->getValue($row, $colMap['jabatan']);
                    
                    if (!empty($namaJabatan)) {
                        $jabatan = JabatanMitra::where('nama_jabatan', trim($namaJabatan))->first();
                        
                        if (!$jabatan) {
                            $errors[] = "Baris $rowNumber: Jabatan '$namaJabatan' tidak ditemukan di tabel jabatan_mitra.";
                            continue; 
                        }

                        $namaSatuan = $this->getValue($row, $colMap['satuan']);
                        $satuan = null;
                        if($namaSatuan) {
                             $satuan = SatuanKegiatan::where('nama_satuan', trim($namaSatuan))->first();
                        }

                        if ($namaSatuan && !$satuan) {
                            $errors[] = "Baris $rowNumber: Satuan '$namaSatuan' tidak ditemukan di database.";
                            continue;
                        }
                        
                        Honorarium::updateOrCreate(
                            [
                                'id_subkegiatan' => $sub->id,
                                'kode_jabatan'   => $jabatan->id, 
                            ],
                            [
                                'tarif'          => preg_replace('/[^0-9]/', '', $this->getValue($row, $colMap['tarif'])) ?: 0,
                                'id_satuan'      => $satuan ? $satuan->id : null,
                                'basis_volume'   => $this->getValue($row, $colMap['basis_volume']) ?: 1,
                                'beban_anggaran' => $this->getValue($row, $colMap['beban_anggaran']), // Bisa NULL jika di Excel kosong
                            ]
                        );
                    }
    
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Baris $rowNumber: " . $e->getMessage();
                }
            }
    
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => "Import Selesai.",
                'successCount' => $successCount,
                'failCount' => count($errors),
                'errors' => $errors
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function formatDate($date) {
        if (empty($date)) return null;
        try {
            if (is_numeric($date)) {
                return Date::excelToDateTimeObject($date)->format('Y-m-d');
            }
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
    
    private function findHeaderIndex($header, $searchTerms)
    {
        foreach ($searchTerms as $term) {
            $index = array_search($term, $header);
            if ($index !== false) return $index;
        }
        return null;
    }
    
    private function getValue($row, $index)
    {
        return ($index !== null && isset($row[$index])) ? trim($row[$index]) : null;
    }
}