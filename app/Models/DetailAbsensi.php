<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailAbsensi extends Model
{
    protected $table = 'detail_absensi';
    protected $fillable = ['id', 'absensi_id', 'tgl', 'masuk', 'keluar', 'lembur', 'kekurangan', 'keterangan_kekurangan', 'status'];
}
