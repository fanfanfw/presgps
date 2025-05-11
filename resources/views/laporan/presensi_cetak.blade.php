<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Presensi {{ date('Y-m-d H:i:s') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
</head>

<body>
    <div class="header" style="margin-bottom: 10px">
        <table>
            <tr>
                <td>
                    @if ($generalsetting->logo && Storage::exists('public/logo/' . $generalsetting->logo))
                        <img src="{{ asset('storage/logo/' . $generalsetting->logo) }}" alt="Logo Perusahaan" style="max-width: 100px;">
                    @else
                        <img src="https://placehold.co/100x100?text=Logo" alt="Logo Default" style="max-width: 100px;">
                    @endif
                </td>
                <td>
                    <h4 style="line-height: 20px; margin-bottom: 5px">
                        LAPORAN PRESENSI
                        <br>
                        {{ $generalsetting->nama_perusahaan }}
                        <br>
                        PERIODE {{ date('d-m-Y', strtotime($periode_dari)) }} - {{ date('d-m-Y', strtotime($periode_sampai)) }}
                    </h4>
                    <span style="font-style: italic;">{{ $generalsetting->alamat }}</span><br>
                    <span style="font-style: italic;">{{ $generalsetting->telepon }}</span>
                </td>
            </tr>
        </table>
    </div>
    <div class="content">
        <table class="datatable3" style="width: 200%">
            <thead>
                <tr>
                    <th rowspan="3">No</th>
                    <th rowspan="3">Nik</th>
                    <th rowspan="3">Nama Karyawan</th>
                    <th rowspan="3">Jabatan</th>
                    <th rowspan="3">Dept</th>
                    <th rowspan="3">Kode Cabang</th>
                    <th colspan="{{ $jmlhari }}">Tanggal</th>
                    <th rowspan="3">Denda</th>
                    <th rowspan="3">Pot. Jam</th>
                </tr>
                <tr>
                    @php
                        $tanggal_presensi = $periode_dari;
                    @endphp
                    @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                        <th style="width: 100px">{{ getHari(date('Y-m-d', strtotime($tanggal_presensi))) }}</th>
                        @php
                            $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                        @endphp
                    @endwhile
                </tr>
                <tr>
                    @php
                        $tanggal_presensi = $periode_dari;
                    @endphp
                    @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                        <th>{{ date('d', strtotime($tanggal_presensi)) }}</th>
                        @php
                            $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                        @endphp
                    @endwhile
                </tr>
            </thead>
            <tbody>
                @foreach ($laporan_presensi as $d)
                    @php
                        $tanggal_presensi = $periode_dari;
                    @endphp
                    <tr>
                        <td style="width:1%">{{ $loop->iteration }}</td>
                        <td style="width:2%">'{{ $d['nik'] }}</td>
                        <td style="width:5%">{{ $d['nama_karyawan'] }}</td>
                        <td style="width:5%">{{ $d['nama_jabatan'] }}</td>
                        <td style="width:5%">{{ $d['nama_dept'] }}</td>
                        <td style="width:5%">{{ $d['kode_cabang'] }}</td>
                        @php
                            $total_denda = 0;
                            $total_potongan_jam = 0;
                        @endphp
                        @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                            @php
                                $denda = 0;
                                $potongan_jam = 0;
                                $search = [
                                    'nik' => $d['nik'],
                                    'tanggal' => $tanggal_presensi,
                                ];

                                $ceklibur = ceklibur($datalibur, $search);
                            @endphp
                            @if (isset($d[$tanggal_presensi]))
                                @if ($d[$tanggal_presensi]['status'] == 'h')
                                    @php
                                        $bgcolor = '';
                                        $textcolor = '';

                                        $ket_nama_jam_kerja =
                                            '<h4 style="font-weight:bold; margin-bottom:10px">' . $d[$tanggal_presensi]['nama_jam_kerja'] . '</h4>';
                                        $ket_jadwal_kerja =
                                            '<span style="color:blue">' .
                                            date('H:i', strtotime($d[$tanggal_presensi]['jam_masuk'])) .
                                            '-' .
                                            date('H:i', strtotime($d[$tanggal_presensi]['jam_pulang'])) .
                                            '</span>';
                                        $jam_masuk = $tanggal_presensi . ' ' . $d[$tanggal_presensi]['jam_masuk'];
                                        $jam_in = !empty($d[$tanggal_presensi]['jam_in'])
                                            ? date('H:i', strtotime($d[$tanggal_presensi]['jam_in']))
                                            : 'Belum Absen';
                                        $jam_out = !empty($d[$tanggal_presensi]['jam_out'])
                                            ? date('H:i', strtotime($d[$tanggal_presensi]['jam_out']))
                                            : 'Belum Absen';

                                        $color_jam_in = !empty($d[$tanggal_presensi]['jam_in']) ? 'green' : 'red';
                                        $color_jam_out = !empty($d[$tanggal_presensi]['jam_out']) ? 'green' : 'red';

                                        $ket_presensi =
                                            ' <span
                                                style="color:' .
                                            $color_jam_in .
                                            '">' .
                                            $jam_in .
                                            '</span> -
                                            <span
                                                style="color:' .
                                            $color_jam_out .
                                            '">' .
                                            $jam_out .
                                            '</span>';

                                        $terlambat = hitungjamterlambat($d[$tanggal_presensi]['jam_in'], $jam_masuk);
                                        $color_terlambat = $terlambat != null ? $terlambat['color'] : '';
                                        $ket_terlambat =
                                            $terlambat != null
                                                ? '<span
                                                style="color:' .
                                                    $color_terlambat .
                                                    '">' .
                                                    $terlambat['show_laporan'] .
                                                    '</span>'
                                                : '';

                                        if ($terlambat != null) {
                                            if ($terlambat['desimal_terlambat'] < 1) {
                                                $potongan_jam_terlambat = 0;
                                                $denda = hitungdenda($denda_list, $terlambat['menitterlambat']);
                                            } else {
                                                $potongan_jam_terlambat = $terlambat['desimal_terlambat'];
                                                $denda = 0;
                                            }
                                        } else {
                                            $potongan_jam_terlambat = 0;
                                            $denda = 0;
                                        }

                                        $ket_denda = $denda != 0 ? '<span style="color:red">Denda : ' . formatAngka($denda) . '</span>' : '';

                                        $pulangcepat = hitungpulangcepat(
                                            $tanggal_presensi,
                                            $d[$tanggal_presensi]['jam_out'],
                                            $d[$tanggal_presensi]['jam_pulang'],
                                            $d[$tanggal_presensi]['istirahat'],
                                            $d[$tanggal_presensi]['jam_awal_istirahat'],
                                            $d[$tanggal_presensi]['jam_akhir_istirahat'],
                                            $d[$tanggal_presensi]['lintashari'],
                                        );

                                        $ket_pulang_cepat =
                                            $pulangcepat != null ? '<span style="color:red">PC : ' . $pulangcepat . ' Jam </span>' : '';
                                        $color_pulang_cepat = $pulangcepat != null ? 'red' : '';

                                        $potongan_jam = $pulangcepat + $potongan_jam_terlambat;
                                        $ket_potongan_jam = !empty($total_potongan_jam)
                                            ? '<span style="color:red">PJ: ' . formatAngkaDesimal($potongan_jam) . '</span>'
                                            : '';
                                        $ket =
                                            $ket_nama_jam_kerja .
                                            $ket_jadwal_kerja .
                                            '<br>' .
                                            $ket_presensi .
                                            '<br>' .
                                            $ket_terlambat .
                                            '<br>' .
                                            $ket_denda .
                                            $ket_pulang_cepat .
                                            '<br>' .
                                            $ket_potongan_jam;
                                    @endphp
                                @elseif($d[$tanggal_presensi]['status'] == 'i')
                                    @php
                                        $bgcolor = '#dea51f';
                                        $textcolor = 'white';
                                        $ket =
                                            '<h4 style="font-weight: bold; margin-bottom:10px">IZIN</h4><span>' .
                                            $d[$tanggal_presensi]['keterangan_izin_absen'] .
                                            '</span>';
                                    @endphp
                                @elseif($d[$tanggal_presensi]['status'] == 's')
                                    @php
                                        $bgcolor = '#c8075b';
                                        $textcolor = 'white';
                                        $ket =
                                            '<h4 style="font-weight: bold; margin-bottom:10px">SAKIT</h4><span>' .
                                            $d[$tanggal_presensi]['keterangan_izin_sakit'] .
                                            '</span>';
                                    @endphp
                                @elseif($d[$tanggal_presensi]['status'] == 'c')
                                    @php
                                        $bgcolor = '#0164b5';
                                        $textcolor = 'white';
                                        $ket =
                                            '<h4 style="font-weight: bold; margin-bottom:10px">CUTI</h4><span>' .
                                            $d[$tanggal_presensi]['keterangan_izin_cuti'] .
                                            '</span>';
                                    @endphp
                                @endif
                            @else
                                @php
                                    $bgcolor = 'red';
                                    $textcolor = 'white';
                                    $ket = '';
                                    //var_dump($ceklibur);
                                    if (!empty($ceklibur)) {
                                        $bgcolor = 'green';
                                        $textcolor = 'white';
                                        $ket = $ceklibur[0]['keterangan'];
                                    }
                                @endphp
                            @endif
                            @php
                                $total_denda += $denda;
                                $total_potongan_jam += $potongan_jam;
                            @endphp
                            <td style="background-color:{{ $bgcolor }}; color:{{ $textcolor }}">
                                {!! $ket !!}
                            </td>
                            @php
                                $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                            @endphp
                        @endwhile
                        <td style="text-align: right">{{ formatAngka($total_denda) }}</td>
                        <td style="text-align: center">{{ formatAngkaDesimal($total_potongan_jam) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
