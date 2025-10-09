<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        // nanti bisa fetch data dari tabel absensi
        return view('laporan.index');
    }
}
