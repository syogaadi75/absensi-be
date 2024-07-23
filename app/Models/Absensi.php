<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['id', 'user_id', 'tgl_mulai', 'tgl_selesai'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
