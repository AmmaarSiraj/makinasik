<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpkSetting extends Model
{
    use HasFactory;

    protected $table = 'spk_setting';

    protected $fillable = [
        'periode',
        'nomor_surat_format',
        'tanggal_surat',
        'nama_ppk',
        'nip_ppk',
        'jabatan_ppk',
        'komponen_honor',
    ];

    protected $casts = [
        'tanggal_surat' => 'date', // Agar otomatis jadi objek Date Carbon
    ];
}