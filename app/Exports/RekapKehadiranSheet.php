<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapKehadiranSheet implements WithEvents, WithTitle
{
    public function __construct(
        public int     $tahun,
        public ?string $namaKelas = null,
        public ?int    $paralel = null,
        public ?string $gender = null,
        public string  $title = 'Rekap Kehadiran'
    ) {}

    public function title(): string { return $this->title; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $sheet = $e->sheet->getDelegate();

                // siswa terfilter
                $siswa = Siswa::query()
                    ->select('siswa.nis','siswa.nama','siswa.gender','k.nama_kelas','k.kelas_paralel')
                    ->join('kelas as k', 'k.id','=','siswa.kelas_id')
                    ->where('siswa.status','A')
                    ->when($this->namaKelas, fn($q)=>$q->where('k.nama_kelas',$this->namaKelas))
                    ->when($this->paralel,   fn($q)=>$q->where('k.kelas_paralel',$this->paralel))
                    ->when(in_array($this->gender,['L','P'],true), fn($q)=>$q->where('siswa.gender',$this->gender))
                    ->orderBy('k.nama_kelas')->orderBy('k.kelas_paralel')->orderBy('siswa.nama')
                    ->get();

                $namaKelas = $this->namaKelas ? ($this->paralel ? "{$this->namaKelas} {$this->paralel}" : $this->namaKelas) : 'Semua';

                // judul
                $sheet->mergeCells('F3:AD3')->setCellValue('F3', 'Rekap Absensi Siswa ..............................');
                $sheet->mergeCells('F4:AD4')->setCellValue('F4', 'Semester Genap Tahun Pembelajaran ........................');
                $sheet->getStyle('F3:AD4')->getFont()->setBold(true);

                // info kelas
                $sheet->setCellValue('B6','Kelas'); $sheet->setCellValue('D6',':'); $sheet->setCellValue('E6',$namaKelas);

                // header dasar
                $sheet->setCellValue('B9','No.');
                $sheet->mergeCells('C9:C10')->setCellValue('C9','NIS');
                $sheet->mergeCells('D9:D10')->setCellValue('D9','Nama');
                $sheet->mergeCells('E9:E10')->setCellValue('E9','L/P');
                $sheet->mergeCells('F9:F10')->setCellValue('F9','Kelas');

                // 12 bulan × H,S,I,A
                $startColIdx = 7; // G
                $colIdx = $startColIdx;
                for ($m=1; $m<=12; $m++) {
                    $bulanLabel = strtoupper(Carbon::createFromDate($this->tahun,$m,1)->translatedFormat('F'));
                    $beg = Coordinate::stringFromColumnIndex($colIdx);
                    $end = Coordinate::stringFromColumnIndex($colIdx + 3);
                    $sheet->mergeCells("{$beg}9:{$end}9")->setCellValue("{$beg}9", $bulanLabel);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx)    .'10','H');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx+1) .'10','S');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx+2) .'10','I');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx+3) .'10','A');
                    $colIdx += 4;
                }

                // total rekap
                $rekapBeg = Coordinate::stringFromColumnIndex($colIdx);
                $rekapEnd = Coordinate::stringFromColumnIndex($colIdx+3);
                $sheet->mergeCells("{$rekapBeg}9:{$rekapEnd}9")->setCellValue("{$rekapBeg}9", 'REKAP ABSEN');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx)    .'10','H');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx+1)  .'10','S');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx+2)  .'10','I');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx+3)  .'10','A');

                $sheet->getStyle('B9:'.$rekapEnd.'10')->getFont()->setBold(true);
                $sheet->getStyle('B9:'.$rekapEnd.'10')->getAlignment()->setHorizontal('center')->setVertical('center');

                // isi
                $row = 11;
                foreach ($siswa as $i => $s) {
                    $sheet->setCellValue("B{$row}", $i+1);
                    $sheet->setCellValue("C{$row}", $s->nis);
                    $sheet->setCellValue("D{$row}", $s->nama);
                    $sheet->setCellValue("E{$row}", $s->gender);
                    $sheet->setCellValue("F{$row}", $s->nama_kelas.' '.$s->kelas_paralel);

                    $col = $startColIdx;
                    $tH=$tS=$tI=$tA=0;

                    for ($m=1; $m<=12; $m++) {
                        $mStart = Carbon::createFromDate($this->tahun,$m,1)->startOfMonth();
                        $mEnd   = (clone $mStart)->endOfMonth();

                        // Agregasi bulanan (tetap sesuai data, tidak mengubah template)
                        $counts = Absensi::query()
                            ->selectRaw("SUM(CASE WHEN status_harian='HADIR' THEN 1 ELSE 0 END) as h,
                                         SUM(CASE WHEN status_harian='SAKIT' THEN 1 ELSE 0 END) as s,
                                         SUM(CASE WHEN status_harian='IZIN'  THEN 1 ELSE 0 END) as i,
                                         SUM(CASE WHEN status_harian='ALPA'  THEN 1 ELSE 0 END) as a")
                            ->where('nis', $s->nis)
                            ->whereBetween('tanggal', [$mStart->toDateString().' 00:00:00', $mEnd->toDateString().' 23:59:59'])
                            ->first();

                        $vals = [(int)$counts->h, (int)$counts->s, (int)$counts->i, (int)$counts->a];
                        $tH += $vals[0]; $tS += $vals[1]; $tI += $vals[2]; $tA += $vals[3];

                        foreach ([0,1,2,3] as $k) {
                            $c = Coordinate::stringFromColumnIndex($col + $k);
                            $sheet->setCellValue("{$c}{$row}", $vals[$k]);
                        }
                        $col += 4;
                    }

                    // total
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col)    ."{$row}", $tH);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1)."{$row}", $tS);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 2)."{$row}", $tI);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 3)."{$row}", $tA);

                    $row++;
                }

                // border + width
                $lastRow = max($row, 11+15);
                $sheet->getStyle('B9:'.$rekapEnd.$lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getColumnDimension('B')->setWidth(4);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(28);
                $sheet->getColumnDimension('E')->setWidth(5);
                $sheet->getColumnDimension('F')->setWidth(8);

                for ($c=$startColIdx; $c<=Coordinate::columnIndexFromString($rekapEnd); $c++) {
                    $colL = Coordinate::stringFromColumnIndex($c);
                    $sheet->getColumnDimension($colL)->setWidth(3.5);
                    $sheet->getStyle("{$colL}11:{$colL}{$lastRow}")
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                }

                $sheet->getStyle($rekapBeg.'9:'.$rekapEnd.'10')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF4B084');
            }
        ];
    }
}
