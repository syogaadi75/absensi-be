<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanJadwal extends Model
{
    protected $table = 'pengaturan_jadwal';
    protected $fillable = ['id', 'jam_masuk', 'jam_keluar'];
}
