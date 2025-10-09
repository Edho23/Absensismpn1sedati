<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;

class KelasController extends Controller
{
    public function index()
    {
        // ambil data kelas (sementara bisa kosong dulu)
        $kelas = Kelas::paginate(10); // kalau model Kelas sudah ada
        return view('kelas.index', compact('kelas'));
    }
}
