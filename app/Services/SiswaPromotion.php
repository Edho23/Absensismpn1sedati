<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class SiswaPromotion
{
    /**
     * Promosikan semua siswa berstatus 'A'.
     * Aturan:
     * - grade 7/8  -> pindah ke (grade+1, paralel sama) JIKA kelas tujuan ada.
     * - grade 9    -> status jadi 'N' (lulus).
     * - Kalau kelas tujuan tidak ada -> skipped (tetap di kelas lama).
     *
     * @return array{moved:int, graduated:int, skipped:int, total:int}
     */
    public function promoteAll(): array
    {
        return DB::transaction(function () {

            // Peta kelas: "grade|paralel" => id
            $targets = [];
            $kelasAll = Kelas::query()->select('id', 'grade', 'kelas_paralel')->get();
            foreach ($kelasAll as $k) {
                $g = (int) $k->grade;
                $p = (int) $k->kelas_paralel;
                if ($g > 0 && $p > 0) {
                    $targets["{$g}|{$p}"] = (int) $k->id;
                }
            }

            $moved = 0;
            $graduated = 0;
            $skipped = 0;

            // Ambil siswa aktif + relasi kelas (hemat memori)
            $rows = Siswa::query()
                ->where('status', 'A')
                ->whereNotNull('kelas_id')
                ->with(['kelas:id,grade,kelas_paralel'])
                ->orderBy('id', 'asc')
                ->cursor();

            foreach ($rows as $s) {
                if (!$s->kelas) { $skipped++; continue; }

                $curGrade   = (int) $s->kelas->grade;
                $curParalel = (int) $s->kelas->kelas_paralel;
                if ($curGrade < 7 || $curGrade > 9 || $curParalel <= 0) {
                    $skipped++; continue;
                }

                // Lulus hanya bila grade === 9
                if ($curGrade === 9) {
                    $s->status = 'N';
                    $s->save();
                    $graduated++;
                    continue;
                }

                // Pindah ke (grade+1, paralel sama) jika ada
                $nextKey = ($curGrade + 1) . '|' . $curParalel;
                if (!isset($targets[$nextKey])) {
                    $skipped++;
                    continue;
                }

                $targetId = $targets[$nextKey];
                if ((int)$s->kelas_id === $targetId) {
                    $skipped++;
                    continue;
                }

                $s->kelas_id = $targetId;
                $s->save();
                $moved++;
            }

            return [
                'moved'     => $moved,
                'graduated' => $graduated,
                'skipped'   => $skipped,
                'total'     => $moved + $graduated + $skipped,
            ];
        });
    }
}
