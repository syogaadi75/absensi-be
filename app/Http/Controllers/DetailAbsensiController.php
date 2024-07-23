<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\DetailAbsensi;
use App\Models\PengaturanJadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetailAbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $limit;

        $query = DetailAbsensi::where('id_absensi', $id)
            ->orderBy('masuk', 'asc')
            ->orderBy('tgl', 'asc')
            ->offset($offset)
            ->limit($limit);
        $absensi = $query->get();

        $total = DetailAbsensi::where('id_absensi', $id)->count();

        $totalPages = ceil($total / $limit);

        $res = [
            'data' => $absensi,
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
        return Response::success($res);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $pengaturanJadwal = PengaturanJadwal::find(1);

        $this->validate($request, [
            'id' => 'required',
            'masuk' => 'required',
            'keluar' => 'required'
        ]);

        $id = $request->input('id');
        $masuk = $request->input('masuk');
        $keluar = $request->input('keluar');

        $keluarTime = Carbon::parse($keluar);
        $jadwalKeluarTime = Carbon::parse($pengaturanJadwal->jam_keluar);

        $differenceInMinutes = $jadwalKeluarTime->diffInMinutes($keluarTime, false);


        $kekurangan = 0;
        $lembur = 0;
        if ($differenceInMinutes > 0) {
            $lembur = ceil($differenceInMinutes / 30) * 0.5;
        } else {
            $lembur = 0;
            $kekurangan = ceil($differenceInMinutes / 30) * 0.5;
        }

        $data = [
            'id' => $request->input('id'),
            'masuk' => $masuk,
            'keluar' => $keluar,
            'lembur' => $lembur,
            'status' => 'hadir',
            'kekurangan' => abs($kekurangan)
        ];

        if ($differenceInMinutes < 0) {
            $data['status'] = 'izin';
        }

        if ($request->input('keterangan_kekurangan')) {
            $data['keterangan_kekurangan'] = $request->input('keterangan_kekurangan');
        }

        DB::beginTransaction();
        try {
            $detailAbsensi = DetailAbsensi::find($id);
            $detailAbsensi->update($data);

            DB::commit();
            return Response::success($detailAbsensi, 'Jadwal berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DetailAbsensi  $detailAbsensi
     * @return \Illuminate\Http\Response
     */
    public function show(DetailAbsensi $detailAbsensi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DetailAbsensi  $detailAbsensi
     * @return \Illuminate\Http\Response
     */
    public function edit(DetailAbsensi $detailAbsensi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DetailAbsensi  $detailAbsensi
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DetailAbsensi $detailAbsensi)
    {
        //
    }

    public function updateLibur($id)
    {
        DB::beginTransaction();
        try {
            $detailAbsensi = DetailAbsensi::find($id);
            $pengaturanJadwal = PengaturanJadwal::find(1);
            if (!$detailAbsensi) {
                return Response::error('Data tidak ditemukan', 404);
            }
            $detailAbsensi->masuk = $pengaturanJadwal->jam_masuk;
            $detailAbsensi->status = 'libur';
            $detailAbsensi->save();

            DB::commit();
            return Response::success($detailAbsensi, 'Status berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DetailAbsensi  $detailAbsensi
     * @return \Illuminate\Http\Response
     */
    public function destroy(DetailAbsensi $detailAbsensi)
    {
        //
    }
}
