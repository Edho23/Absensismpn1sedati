namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Kelas,Siswa,KartuRfid};

class DemoSeeder extends Seeder {
  public function run(): void {
    $k7a = Kelas::firstOrCreate(['nama_kelas'=>'7A'], ['tingkat'=>7]);
    $s = Siswa::firstOrCreate(['nisn'=>'1234567890'], ['nama'=>'Budi','id_kelas'=>$k7a->id]);
    KartuRfid::firstOrCreate(['uid'=>'A1 B2 C3 D4'], ['id_siswa'=>$s->id, 'aktif'=>true]);
  }
}
