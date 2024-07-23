<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\Absensi;
use App\Models\DetailAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $limit;

        $absensi = Absensi::offset($offset)->limit($limit)->get();

        $total = Absensi::count();

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
        $absensi = new Absensi();
        $absensi->id_user = Auth::user()->id;
        $absensi->tgl_mulai = $request->input('tgl_mulai');
        $absensi->tgl_selesai = $request->input('tgl_selesai');
        DB::beginTransaction();
        try {
            $absensi->save();

            $startDate = \Carbon\Carbon::parse($absensi->tgl_mulai);
            $endDate = \Carbon\Carbon::parse($absensi->tgl_selesai);

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $detailAbsensi = new DetailAbsensi();
                $detailAbsensi->id_absensi = $absensi->id;
                $detailAbsensi->tgl = $date->toDateString();
                $detailAbsensi->status = 'pending';
                $detailAbsensi->save();
            }

            DB::commit();
            return Response::success($absensi, 'Absensi berhasil ditambahkan');
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::error($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Absensi  $absensi
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $absensi = DB::select('SELECT a.*, u.name as nama_user FROM absensi a JOIN users u ON a.id_user = u.id WHERE a.id = ?', [$id])[0];
        if (!$absensi) {
            return Response::error('Absensi tidak ditemukan', 404);
        }

        return Response::success($absensi);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Absensi  $absensi
     * @return \Illuminate\Http\Response
     */
    public function edit(Absensi $absensi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Absensi  $absensi
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Absensi $absensi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Absensi  $absensi
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $absensi = Absensi::find($id);
        if (!$absensi) {
            return Response::error('Absensi tidak ditemukan', 404);
        }

        DB::beginTransaction();
        try {
            $absensi->delete();
            DetailAbsensi::where('id_absensi', $id)->delete();
            DB::commit();
            return Response::success($absensi, 'Absensi berhasil dihapus');
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::error($th->getMessage());
        }
    }

    public function exportSingle($id)
    {
        $absensi = DB::select('SELECT a.*, u.name as nama_user FROM absensi a JOIN users u ON a.id_user = u.id WHERE a.id = ?', [$id])[0];
        if (!$absensi) {
            return Response::error('Absensi tidak ditemukan', 404);
        }

        $detailAbsensi = DetailAbsensi::where('id_absensi', $id)->get();

        $export = new AbsensiExport($absensi, $detailAbsensi);
        $filename = 'absensi_' . $id . '.xlsx';

        $response = new BinaryFileResponse(
            Excel::download($export, $filename)->getFile()->getPathname()
        );

        return $response;
    }
}
