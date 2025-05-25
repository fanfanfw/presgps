<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Denda;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function presensi()
    {
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        $cabang = Cabang::orderBy('kode_cabang')->get();
        $departemen = Departemen::orderBy('kode_dept')->get();
        $data['cabang'] = $cabang;
        $data['departemen'] = $departemen;
        return view('laporan.presensi', $data);
    }


    public function cetakpresensi(Request $request)
    {


        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $periode_laporan_dari = $generalsetting->periode_laporan_dari;
        $periode_laporan_sampai = $generalsetting->periode_laporan_sampai;
        $periode_laporan_lintas_bulan = $generalsetting->periode_laporan_next_bulan;


        if ($request->periode_laporan == 1) {
            if ($periode_laporan_lintas_bulan == 1) {
                if ($request->bulan == 1) {
                    $bulan = 12;
                    $tahun = $request->tahun - 1;
                } else {
                    $bulan = $request->bulan - 1;
                    $tahun = $request->tahun;
                }
            } else {
                $bulan = $request->bulan;
                $tahun = $request->tahun;
            }

            // Menambahkan nol di depan bulan jika bulan kurang dari 10

            $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $periode_dari = $tahun . '-' . $bulan . '-' . $periode_laporan_dari;
            $periode_sampai = $request->tahun . '-' . $request->bulan . '-' . $periode_laporan_sampai;
        } else {
            // Menambahkan nol di depan bulan jika bulan kurang dari 10

            $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
            $periode_dari = $request->tahun . '-' . $bulan . '-01';
            $periode_sampai = date('Y-m-t', strtotime($periode_dari));
        }


        $presensi_detail  = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->leftJoin('presensi_izinabsen_approve', 'presensi.id', '=', 'presensi_izinabsen_approve.id_presensi')
            ->leftJoin('presensi_izinabsen', 'presensi_izinabsen_approve.kode_izin', '=', 'presensi_izinabsen.kode_izin')
            ->leftJoin('presensi_izinsakit_approve', 'presensi.id', '=', 'presensi_izinsakit_approve.id_presensi')
            ->leftJoin('presensi_izinsakit', 'presensi_izinsakit_approve.kode_izin_sakit', '=', 'presensi_izinsakit.kode_izin_sakit')
            ->leftJoin('presensi_izincuti_approve', 'presensi.id', '=', 'presensi_izincuti_approve.id_presensi')
            ->leftJoin('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->select(
                'presensi.*',
                'nama_jam_kerja',
                'jam_masuk',
                'jam_pulang',
                'istirahat',
                'jam_awal_istirahat',
                'jam_akhir_istirahat',
                'lintashari',
                'presensi_izinabsen.keterangan as keterangan_izin_absen',
                'presensi_izinsakit.keterangan as keterangan_izin_sakit',
                'presensi_izincuti.keterangan as keterangan_izin_cuti'
            )
            ->whereBetween('presensi.tanggal', [$periode_dari, $periode_sampai]);

        $q_presensi = Karyawan::query();
        $q_presensi->select(
            'karyawan.nik',
            'nama_karyawan',
            'nama_jabatan',
            'nama_dept',
            'karyawan.kode_cabang',
            'presensi.tanggal',
            'presensi.status',
            'presensi.kode_jam_kerja',
            'presensi.nama_jam_kerja',
            'presensi.jam_masuk',
            'presensi.jam_pulang',
            'presensi.jam_in',
            'presensi.jam_out',
            'presensi.istirahat',
            'presensi.jam_awal_istirahat',
            'presensi.jam_akhir_istirahat',
            'presensi.lintashari',
            'presensi.keterangan_izin_absen',
            'presensi.keterangan_izin_sakit',
            'presensi.keterangan_izin_cuti'
        );
        $q_presensi->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $q_presensi->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $q_presensi->leftJoinSub($presensi_detail, 'presensi', function ($join) {
            $join->on('karyawan.nik', '=', 'presensi.nik');
        });
        if (!empty($request->kode_cabang)) {
            $q_presensi->where('karyawan.kode_cabang', $request->kode_cabang);
        }
        if (!empty($request->kode_dept)) {
            $q_presensi->where('karyawan.kode_dept', $request->kode_dept);
        }

        if (!empty($request->nik)) {
            $q_presensi->where('karyawan.nik', $request->nik);
        }
        $q_presensi->orderBy('karyawan.nama_karyawan');
        $q_presensi->orderBy('presensi.tanggal', 'asc');
        $presensi = $q_presensi->get();


        $data['periode_dari'] = $periode_dari;
        $data['periode_sampai'] = $periode_sampai;
        $data['jmlhari'] = hitungJumlahHari($periode_dari, $periode_sampai) + 1;
        $data['denda_list'] = Denda::all()->toArray();
        $data['datalibur'] = getdatalibur($periode_dari, $periode_sampai);
        $data['generalsetting'] = $generalsetting;

        if (isset($_POST['exportButton'])) {
            header("Content-type: application/vnd-ms-excel");
            // Mendefinisikan nama file ekspor "-SahabatEkspor.xls"
            header("Content-Disposition: attachment; filename=Rekap Presensi $periode_dari - $periode_sampai.xls");
        }
        if (!empty($request->nik)) {
            $karyawan = Karyawan::join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
                ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
                ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
                ->where('karyawan.nik', $request->nik)
                ->first();
            $data['karyawan'] = $karyawan;
            $data['presensi'] = $presensi;
            return view('laporan.presensi_karyawan_cetak', $data);
        } else {
            $laporan_presensi = $presensi->groupBy('nik')->map(function ($rows) {
                $data = [
                    'nik' => $rows->first()->nik,
                    'nama_karyawan' => $rows->first()->nama_karyawan,
                    'nama_jabatan' => $rows->first()->nama_jabatan,
                    'nama_dept' => $rows->first()->nama_dept,
                    'kode_cabang' => $rows->first()->kode_cabang
                ];
                foreach ($rows as $row) {
                    $data[$row->tanggal] = [
                        'status' => $row->status,
                        'kode_jam_kerja' => $row->kode_jam_kerja,
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                        'jam_in' => $row->jam_in,
                        'jam_out' => $row->jam_out,
                        'istirahat' => $row->istirahat,
                        'jam_awal_istirahat' => $row->jam_awal_istirahat,
                        'jam_akhir_istirahat' => $row->jam_akhir_istirahat,
                        'lintashari' => $row->lintashari,
                        'keterangan_izin_absen' => $row->keterangan_izin_absen,
                        'keterangan_izin_sakit' => $row->keterangan_izin_sakit,
                        'keterangan_izin_cuti' => $row->keterangan_izin_cuti
                    ];
                }
                return $data;
            });
            $data['laporan_presensi'] = $laporan_presensi;
            return view('laporan.presensi_cetak', $data);
        }
    }
}
