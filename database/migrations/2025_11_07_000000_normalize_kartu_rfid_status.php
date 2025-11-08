<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Tambah kolom status kalau belum ada (nullable dulu)
        if (!Schema::hasColumn('kartu_rfid', 'status')) {
            Schema::table('kartu_rfid', function (Blueprint $t) {
                $t->char('status', 1)->nullable()->after('nis'); // 'A'|'N'
            });
        }

        // 2) Backfill dari status_aktif → status
        $driver = DB::getDriverName(); // 'pgsql', 'mysql', 'sqlite', ...
        if (Schema::hasColumn('kartu_rfid', 'status_aktif')) {
            if ($driver === 'pgsql') {
                // PostgreSQL: boolean langsung, pakai COALESCE
                DB::statement("UPDATE kartu_rfid
                               SET status = CASE WHEN COALESCE(status_aktif, FALSE) THEN 'A' ELSE 'N' END");
            } elseif ($driver === 'mysql') {
                // MySQL: tinyint(1), gunakan COALESCE(...,0)=1
                DB::statement("UPDATE `kartu_rfid`
                               SET `status` = CASE WHEN COALESCE(`status_aktif`,0)=1 THEN 'A' ELSE 'N' END");
            } else {
                // Fallback aman via query builder (tanpa raw operator spesifik)
                DB::table('kartu_rfid')->orderBy('id')->chunkById(1000, function ($rows) {
                    foreach ($rows as $row) {
                        $st = (!empty($row->status_aktif)) ? 'A' : 'N';
                        DB::table('kartu_rfid')->where('id', $row->id)->update(['status' => $st]);
                    }
                });
            }
        } else {
            // Kalau tidak ada status_aktif, isi default 'A'
            DB::table('kartu_rfid')->whereNull('status')->update(['status' => 'A']);
        }

        // 3) Pastikan tidak ada NULL yang tersisa
        DB::table('kartu_rfid')->whereNull('status')->update(['status' => 'A']);

        // 4) Jadikan NOT NULL + DEFAULT sesuai driver (tanpa doctrine/dbal)
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE kartu_rfid ALTER COLUMN status SET DEFAULT 'A'");
            DB::statement("ALTER TABLE kartu_rfid ALTER COLUMN status SET NOT NULL");
            DB::statement("CREATE INDEX IF NOT EXISTS kartu_rfid_status_index ON kartu_rfid(status)");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE `kartu_rfid` MODIFY `status` CHAR(1) NOT NULL DEFAULT 'A'");
            DB::statement("CREATE INDEX `kartu_rfid_status_index` ON `kartu_rfid`(`status`)");
        } else {
            // Fallback: coba lewat schema builder semampunya (tidak semua DB support change)
            try {
                Schema::table('kartu_rfid', function (Blueprint $t) {
                    $t->char('status', 1)->default('A')->nullable(false);
                    $t->index('status', 'kartu_rfid_status_index');
                });
            } catch (\Throwable $e) {
                // Abaikan jika tidak didukung; minimal datanya sudah terisi 'A'
            }
        }

        // 5) Hapus kolom legacy status_aktif (jika masih ada)
        if (Schema::hasColumn('kartu_rfid', 'status_aktif')) {
            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE kartu_rfid DROP COLUMN IF EXISTS status_aktif');
            } elseif ($driver === 'mysql') {
                DB::statement('ALTER TABLE `kartu_rfid` DROP COLUMN `status_aktif`');
            } else {
                try {
                    Schema::table('kartu_rfid', function (Blueprint $t) {
                        $t->dropColumn('status_aktif');
                    });
                } catch (\Throwable $e) {
                    // kalau DB tidak support dropColumn via schema builder, abaikan
                }
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        // 1) Tambah balik status_aktif
        if (!Schema::hasColumn('kartu_rfid', 'status_aktif')) {
            Schema::table('kartu_rfid', function (Blueprint $t) {
                $t->boolean('status_aktif')->default(true)->after('nis');
            });
        }

        // 2) Backfill dari status → status_aktif
        if ($driver === 'pgsql') {
            DB::statement("UPDATE kartu_rfid
                           SET status_aktif = CASE WHEN status = 'A' THEN TRUE ELSE FALSE END");
        } elseif ($driver === 'mysql') {
            DB::statement("UPDATE `kartu_rfid`
                           SET `status_aktif` = CASE WHEN `status`='A' THEN 1 ELSE 0 END");
        } else {
            DB::table('kartu_rfid')->orderBy('id')->chunkById(1000, function ($rows) {
                foreach ($rows as $row) {
                    $bool = ($row->status === 'A') ? 1 : 0;
                    DB::table('kartu_rfid')->where('id', $row->id)->update(['status_aktif' => $bool]);
                }
            });
        }

        // 3) Hapus index & kolom status
        if ($driver === 'pgsql') {
            DB::statement("DROP INDEX IF EXISTS kartu_rfid_status_index");
            DB::statement("ALTER TABLE kartu_rfid DROP COLUMN IF EXISTS status");
        } elseif ($driver === 'mysql') {
            DB::statement("DROP INDEX `kartu_rfid_status_index` ON `kartu_rfid`");
            DB::statement("ALTER TABLE `kartu_rfid` DROP COLUMN `status`");
        } else {
            try {
                Schema::table('kartu_rfid', function (Blueprint $t) {
                    $t->dropIndex('kartu_rfid_status_index');
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('kartu_rfid', function (Blueprint $t) {
                    $t->dropColumn('status');
                });
            } catch (\Throwable $e) {}
        }
    }
};
