<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Honorarium extends Model
{
    use HasFactory;

    protected $table = 'honorarium';

    protected $fillable = [
        'id_subkegiatan',
        'kode_jabatan',
        'tarif',
        'id_satuan',
        'basis_volume',
        'beban_anggaran'
    ];

    // Relasi ke Subkegiatan
    public function subkegiatan()
    {
        return $this->belongsTo(Subkegiatan::class, 'id_subkegiatan', 'id');
    }

    // Relasi ke Jabatan Mitra
    public function jabatan()
    {
        return $this->belongsTo(JabatanMitra::class, 'kode_jabatan', 'kode_jabatan');
    }
}