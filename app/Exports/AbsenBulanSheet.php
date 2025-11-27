<?php

namespace App\Exports;

use App\Exports\Concerns\UsesHariLibur;
use App\Models\Absensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AbsenBulanSheet implements WithEvents, WithTitle
{
    use UsesHariLibur;

    public function __construct(
        public int     $bulan,
        public int     $tahun,
        public ?string $namaKelas = null,
        public ?int    $paralel = null,
        public ?string $gender = null,
        public ?string $waliKelas = null,
        public string  $title = 'Absen'
    ) {}

    public function title(): string { return $this->title; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $sheet = $e->sheet->getDelegate();

                // ====== RANGE BULAN ======
                $start = Carbon::createFromDate($this->tahun, $this->bulan, 1)->startOfMonth();
                $end   = (clone $start)->endOfMonth();
                $days  = (int)$start->daysInMonth;
                $bulanLabel = $start->translatedFormat('F');

                // ====== DATA SISWA TERFILTER ======
                $siswa = Siswa::query()
                    ->select('siswa.nis', 'siswa.nama', 'siswa.gender', 'k.nama_kelas', 'k.kelas_paralel')
                    ->join('kelas as k', 'k.id', '=', 'siswa.kelas_id')
                    ->where('siswa.status', 'A')
                    ->when($this->namaKelas, fn($q) => $q->where('k.nama_kelas', $this->namaKelas))
                    ->when($this->paralel,   fn($q) => $q->where('k.kelas_paralel', $this->paralel))
                    ->when(in_array($this->gender, ['L','P'], true), fn($q) => $q->where('siswa.gender', $this->gender))
                    ->orderBy('k.nama_kelas')->orderBy('k.kelas_paralel')->orderBy('siswa.nama')
                    ->get();

                // Ringkas jumlah
                $jml   = $siswa->count();
                $laki  = $siswa->where('gender','L')->count();
                $perem = $siswa->where('gender','P')->count();

                $namaKelas = $this->namaKelas ? ($this->paralel ? "{$this->namaKelas} {$this->paralel}" : $this->namaKelas) : 'Semua';

                // ====== HEADER (TEMPLATE TETAP) ======
                $sheet->mergeCells('F3:Q3');
                $sheet->setCellValue('F3', 'Daftar Hadir Siswa ............................');
                $sheet->mergeCells('F4:Q4');
                $sheet->setCellValue('F4', 'Semester Genap Tahun Pembelajaran .....................');
                $sheet->getStyle('F3:Q4')->getFont()->setBold(true);

                $sheet->setCellValue('B6', 'Kelas');         $sheet->setCellValue('D6', ':'); $sheet->setCellValue('E6', $namaKelas);
                $sheet->setCellValue('G6', 'Jumlah Siswa');  $sheet->setCellValue('I6', ':'); $sheet->setCellValue('J6', $jml.' orang');
                $sheet->setCellValue('L6', 'Laki-Laki');     $sheet->setCellValue('N6', ':'); $sheet->setCellValue('O6', $laki.' orang');
                $sheet->setCellValue('Q6', 'Perempuan');     $sheet->setCellValue('S6', ':'); $sheet->setCellValue('T6', $perem.' orang');
                $sheet->setCellValue('V6', 'Wali Kelas');    $sheet->setCellValue('X6', ':'); $sheet->setCellValue('Y6', $this->waliKelas ?: '....................');

                // ====== HEADER TABEL ======
                $sheet->setCellValue('B9', 'No.');
                $sheet->mergeCells('C9:C10')->setCellValue('C9', 'NIS');
                $sheet->mergeCells('D9:D10')->setCellValue('D9', 'Nama');
                $sheet->mergeCells('E9:E10')->setCellValue('E9', 'L/P');
                $sheet->mergeCells('F9:F10')->setCellValue('F9', 'Kelas');

                // Kolom tanggal mulai di G
                $startColIndex    = 7; // G
                $rekapStartColIdx = $startColIndex + $days; // setelah tanggal
                $rekapEndColIdx   = $rekapStartColIdx + 3;  // H S I A

                // Judul bulan (baris 9)
                $bulanStart = Coordinate::stringFromColumnIndex($startColIndex);
                $bulanEnd   = Coordinate::stringFromColumnIndex($rekapStartColIdx - 1);
                $sheet->mergeCells("{$bulanStart}9:{$bulanEnd}9");
                $sheet->setCellValue("{$bulanStart}9", strtoupper($bulanLabel));

                // Angka tanggal (baris 10)
                for ($d = 1; $d <= $days; $d++) {
                    $col = Coordinate::stringFromColumnIndex($startColIndex + ($d - 1));
                    $sheet->setCellValue("{$col}10", $d);
                }

                // Rekap H S I A
                $rekapTitleCol = Coordinate::stringFromColumnIndex($rekapStartColIdx);
                $rekapEndCol   = Coordinate::stringFromColumnIndex($rekapEndColIdx);
                $sheet->mergeCells("{$rekapTitleCol}9:{$rekapEndCol}9")->setCellValue("{$rekapTitleCol}9", 'Rekap');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($rekapStartColIdx)     . '10', 'H');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($rekapStartColIdx + 1) . '10', 'S');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($rekapStartColIdx + 2) . '10', 'I');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($rekapStartColIdx + 3) . '10', 'A');

                // nomor "No" baris 10
                $sheet->setCellValue('B10', 'No');

                // Bold & center header
                $sheet->getStyle("B9:{$rekapEndCol}10")->getFont()->setBold(true);
                $sheet->getStyle("B9:{$rekapEndCol}10")->getAlignment()->setHorizontal('center')->setVertical('center');
                $sheet->getRowDimension(10)->setRowHeight(18);

                // ====== HARI LIBUR (MINGGU + DB) ======
                $holidayDays = $this->holidayDays($this->bulan, $this->tahun);
                // Warnai header tanggal libur menjadi merah
                $this->paintHolidayHeader($sheet, $holidayDays, 10, $startColIndex);

                // ====== ISI DATA ======
                $firstRow = 11;
                $row      = $firstRow;

                foreach ($siswa as $i => $s) {
                    $sheet->setCellValue("B{$row}", $i + 1);
                    $sheet->setCellValue("C{$row}", $s->nis);
                    $sheet->setCellValue("D{$row}", $s->nama);
                    $sheet->setCellValue("E{$row}", $s->gender);
                    $sheet->setCellValue("F{$row}", $s->nama_kelas . ' ' . $s->kelas_paralel);

                    $kehadiran = Absensi::query()
                        ->selectRaw('EXTRACT(DAY FROM tanggal) as d, status_harian')
                        ->where('nis', $s->nis)
                        ->whereBetween(
                            'tanggal',
                            [$start->toDateString().' 00:00:00', $end->toDateString().' 23:59:59']
                        )
                        ->get()
                        ->keyBy('d');

                    $h = $sa = $iz = $al = 0;

                    for ($d = 1; $d <= $days; $d++) {
                        // Jika libur (Minggu/DB) → SKIP (tidak dihitung & tidak diisi)
                        if ($this->isHolidayDay($d, $holidayDays)) {
                            continue;
                        }

                        $status = $kehadiran->get($d)?->status_harian;
                        $mark   = '';
                        if ($status === 'HADIR')      { $mark = 'H'; $h++; }
                        elseif ($status === 'SAKIT')  { $mark = 'S'; $sa++; }
                        elseif ($status === 'IZIN')   { $mark = 'I'; $iz++; }
                        elseif ($status === 'ALPA')   { $mark = 'A'; $al++; }

                        if ($mark !== '') {
                            $col = Coordinate::stringFromColumnIndex($startColIndex + ($d - 1));
                            $sheet->setCellValue("{$col}{$row}", $mark);
                        }
                    }

                    // Rekap total
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($rekapStartColIdx)     . $row, $h);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($rekapStartColIdx + 1) . $row, $sa);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($rekapStartColIdx + 2) . $row, $iz);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($rekapStartColIdx + 3) . $row, $al);

                    $row++;
                }

                // ====== BORDER & WIDTH ======
                $lastRow = max($row, $firstRow + 15);
                $sheet->getStyle("B9:{$rekapEndCol}{$lastRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getColumnDimension('B')->setWidth(4);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(28);
                $sheet->getColumnDimension('E')->setWidth(5);
                $sheet->getColumnDimension('F')->setWidth(8);

                for ($c = $startColIndex; $c <= $rekapStartColIdx + 3; $c++) {
                    $col = Coordinate::stringFromColumnIndex($c);
                    $sheet->getColumnDimension($col)->setWidth(3.5);
                    $sheet->getStyle("{$col}11:{$col}{$lastRow}")
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                }

                // header rekap total (oranye muda)
                $sheet->getStyle("{$rekapTitleCol}9:{$rekapEndCol}10")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF4B084');

                $sheet->getStyle('B9:F10')->getAlignment()->setHorizontal('center')->setVertical('center');
            }
        ];
    }
}
