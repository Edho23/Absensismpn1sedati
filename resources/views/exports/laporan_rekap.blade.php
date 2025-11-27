<table border="1" cellspacing="0" cellpadding="4" style="border-collapse: collapse; font-family: Arial; font-size: 10pt;">
    {{-- Baris info kelas & jumlah siswa --}}
    <tr>
        <td colspan="{{ 6 + count($months)*4 + 4 }}" style="font-weight: bold;">
            Kelas : {{ $kelasLabel }} &nbsp;&nbsp;&nbsp;
            Jumlah Siswa : {{ $jumlahSiswa }} orang &nbsp;&nbsp;&nbsp;
            Laki-Laki : {{ $jumlahL }} orang &nbsp;&nbsp;&nbsp;
            Perempuan : {{ $jumlahP }} orang &nbsp;&nbsp;&nbsp;
            (Periode: {{ $periodeText }})
        </td>
    </tr>

    {{-- Header utama --}}
    <tr style="background-color:#cfe2ff; font-weight:bold; text-align:center;">
        <td rowspan="2">No.</td>
        <td rowspan="2">NIS</td>
        <td rowspan="2">Nama</td>
        <td rowspan="2">L/P</td>
        <td rowspan="2">Kelas</td>
        <td rowspan="2">Paralel</td>

        @foreach($months as $m)
            <td colspan="4">{{ $m['label'] }}</td>
        @endforeach

        <td colspan="4">REKAP ABSEN</td>
    </tr>

    {{-- Sub-header H / S / I / A --}}
    <tr style="background-color:#e7f1ff; font-weight:bold; text-align:center;">
        @foreach($months as $m)
            <td>H</td>
            <td>S</td>
            <td>I</td>
            <td>A</td>
        @endforeach
        <td>H</td>
        <td>S</td>
        <td>I</td>
        <td>A</td>
    </tr>

    {{-- Data per siswa --}}
    @php $no = 1; @endphp
    @foreach($students as $s)
        @php
            $nis = $s->nis;
            $genderCol = isset($s->gender) ? 'gender' : (isset($s->jenis_kelamin) ? 'jenis_kelamin' : null);
            $lp = $genderCol ? ($s->$genderCol ?? '-') : '-';

            $totalH = 0; $totalS = 0; $totalI = 0; $totalA = 0;
        @endphp
        <tr>
            <td style="text-align:center;">{{ $no++ }}</td>
            <td>{{ $s->nis }}</td>
            <td>{{ $s->nama }}</td>
            <td style="text-align:center;">{{ $lp }}</td>
            <td style="text-align:center;">{{ $s->kelas->nama_kelas ?? '' }}</td>
            <td style="text-align:center;">{{ $s->kelas->kelas_paralel ?? '' }}</td>

            @foreach($months as $idx => $m)
                @php
                    $cell = $matrix[$nis][$idx] ?? ['H'=>0,'S'=>0,'I'=>0,'A'=>0];
                    $totalH += $cell['H'];
                    $totalS += $cell['S'];
                    $totalI += $cell['I'];
                    $totalA += $cell['A'];
                @endphp
                <td style="text-align:center;">{{ $cell['H'] }}</td>
                <td style="text-align:center;">{{ $cell['S'] }}</td>
                <td style="text-align:center;">{{ $cell['I'] }}</td>
                <td style="text-align:center;">{{ $cell['A'] }}</td>
            @endforeach

            {{-- Rekap total per siswa --}}
            <td style="text-align:center; font-weight:bold;">{{ $totalH }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $totalS }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $totalI }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $totalA }}</td>
        </tr>
    @endforeach
</table>
