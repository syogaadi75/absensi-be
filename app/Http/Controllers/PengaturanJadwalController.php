<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\PengaturanJadwal;
use Illuminate\Http\Request;

class PengaturanJadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pengaturanJadwal = PengaturanJadwal::find(1);
        return Response::success($pengaturanJadwal);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PengaturanJadwal  $pengaturanJadwal
     * @return \Illuminate\Http\Response
     */
    public function show(PengaturanJadwal $pengaturanJadwal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PengaturanJadwal  $pengaturanJadwal
     * @return \Illuminate\Http\Response
     */
    public function edit(PengaturanJadwal $pengaturanJadwal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PengaturanJadwal  $pengaturanJadwal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PengaturanJadwal $pengaturanJadwal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PengaturanJadwal  $pengaturanJadwal
     * @return \Illuminate\Http\Response
     */
    public function destroy(PengaturanJadwal $pengaturanJadwal)
    {
        //
    }
}
