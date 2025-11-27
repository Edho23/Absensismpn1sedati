<?php

namespace App\Exports\Concerns;

use App\Models\HariLibur;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait UsesHariLibur
{
    /**
     * Ambil daftar hari (1..31) yang merupakan hari libur untuk bulan & tahun tertentu.
     * Sumber:
     * 1) Tabel hari_libur (tanggal + berulang)
     * 2) Otomatis semua hari MINGGU pada bulan tsb (tanpa perlu input)
     *
     * @return int[] mis. [1, 7, 14, 21, 28, 25] (gabungan Minggu + libur nasional/kustom)
     */
    protected function holidayDays(int $bulan, int $tahun): array
    {
        $start = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $days = [];

        // 1) Semua hari MINGGU bulan ini (auto)
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if ($cursor->isSunday()) {
                $days[] = (int) $cursor->day;
            }
            $cursor->addDay();
        }

        // 2) Libur dari database (berulang & non-berulang)
        $rows = HariLibur::query()->get();
        foreach ($rows as $r) {
            $tgl = Carbon::parse($r->tanggal);

            if ($r->berulang) {
                // Berlaku tiap tahun pada dd-mm yang sama
                if ((int) $tgl->month === (int) $bulan) {
                    $days[] = (int) $tgl->day;
                }
            } else {
                // Spesifik tanggal & tahun
                if ($tgl->betweenIncluded($start, $end)) {
                    $days[] = (int) $tgl->day;
                }
            }
        }

        // Unik & urut
        $days = array_values(array_unique(array_filter($days, fn ($d) => $d >= 1)));
        sort($days);
        return $days;
    }

    /**
     * Helper: apakah hari (1..31) ini libur?
     */
    protected function isHolidayDay(int $day, array $holidayDays): bool
    {
        return in_array($day, $holidayDays, true);
    }

    /**
     * Warnai header tanggal yang libur (Minggu & libur DB) dengan merah.
     *
     * @param Worksheet $sheet
     * @param int[]     $holidayDays
     * @param int       $rowHeaderTanggal  baris angka tanggal (di template = 10)
     * @param int       $startColIndex     indeks kolom awal tanggal (G = 7)
     */
    protected function paintHolidayHeader(Worksheet $sheet, array $holidayDays, int $rowHeaderTanggal, int $startColIndex): void
    {
        if (empty($holidayDays)) return;

        foreach ($holidayDays as $d) {
            $col = Coordinate::stringFromColumnIndex($startColIndex + ($d - 1));
            $sheet->getStyle("{$col}{$rowHeaderTanggal}")
                ->getFill()->setFillType(Fill::FILL_SOLID)  
                ->getStartColor()->setARGB('ff0000'); // merah muda lembut
        }
    }
}
