<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanRekapExport implements WithMultipleSheets
{
    public function __construct(
        public int     $bulan,
        public int     $tahun,
        public ?string $namaKelas = null,
        public ?int    $paralel = null,
        public ?string $gender = null,
        public string  $jenis = 'bulan',
        public ?string $waliKelas = null
    ) {}

    private function normalizedJenis(): string
    {
        return in_array($this->jenis, ['bulan','bulan_rekap','all_rekap'], true) ? $this->jenis : 'bulan';
    }

    public function sheets(): array
    {
        $jenis = $this->normalizedJenis(); // ← gunakan yang sudah aman
        $sheets = [];

        $bulanName = \Carbon\Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F');

        if ($jenis === 'bulan') {
            $sheets[] = new AbsenBulanSheet(
                bulan: $this->bulan, tahun: $this->tahun,
                namaKelas: $this->namaKelas, paralel: $this->paralel, gender: $this->gender,
                waliKelas: $this->waliKelas, title: "Absen {$bulanName}"
            );
        } elseif ($jenis === 'bulan_rekap') {
            $sheets[] = new AbsenBulanSheet(
                bulan: $this->bulan, tahun: $this->tahun,
                namaKelas: $this->namaKelas, paralel: $this->paralel, gender: $this->gender,
                waliKelas: $this->waliKelas, title: "Absen {$bulanName}"
            );
            $sheets[] = new RekapKehadiranSheet(
                tahun: $this->tahun,
                namaKelas: $this->namaKelas, paralel: $this->paralel, gender: $this->gender,
                title: 'Rekap Kehadiran'
            );
        } else { // all_rekap
            for ($m=1; $m<=12; $m++) {
                $nm = \Carbon\Carbon::createFromDate($this->tahun, $m, 1)->translatedFormat('F');
                $sheets[] = new AbsenBulanSheet(
                    bulan: $m, tahun: $this->tahun,
                    namaKelas: $this->namaKelas, paralel: $this->paralel, gender: $this->gender,
                    waliKelas: $this->waliKelas, title: "Absen {$nm}"
                );
            }
            $sheets[] = new RekapKehadiranSheet(
                tahun: $this->tahun,
                namaKelas: $this->namaKelas, paralel: $this->paralel, gender: $this->gender,
                title: 'Rekap Kehadiran'
            );
        }

        if (empty($sheets)) { // safety
            $sheets[] = new AbsenBulanSheet(
                bulan: $this->bulan, tahun: $this->tahun,
                namaKelas: $this->namaKelas, paralel: $this->paralel, gender: $this->gender,
                waliKelas: $this->waliKelas, title: "Absen {$bulanName}"
            );
        }

        return $sheets;
    }
}
