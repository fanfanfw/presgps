<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Denda;
use App\Models\Detailharilibur;
use App\Models\Detailsetjamkerjabydept;
use App\Models\Facerecognition;
use App\Models\Harilibur;
use App\Models\Jamkerja;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\Setjamkerjabydept;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{

    public function index(Request $request)
    {

        $tanggal = !empty($request->tanggal) ? $request->tanggal : date('Y-m-d');
        $presensi = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi.id',
                'presensi.nik',
                'presensi.tanggal',
                'presensi.kode_jam_kerja',
                'nama_jam_kerja',
                'jam_masuk',
                'jam_pulang',
                'istirahat',
                'jam_awal_istirahat',
                'jam_akhir_istirahat',
                'jam_in',
                'foto_in',
                'jam_out',
                'foto_out',
                'status',
                'lintashari'
            )
            ->where('presensi.tanggal', $tanggal);

        $query = Karyawan::query();
        $query->select(
            'presensi.id',
            'karyawan.nik',
            'nama_karyawan',
            'kode_dept',
            'kode_cabang',
            'presensi.tanggal as tanggal_presensi',
            'presensi.jam_in',
            'presensi.kode_jam_kerja',
            'nama_jam_kerja',
            'jam_masuk',
            'jam_pulang',
            'istirahat',
            'jam_awal_istirahat',
            'jam_akhir_istirahat',
            'jam_in',
            'jam_out',
            'status',
            'foto_in',
            'foto_out',
            'lintashari',
            'karyawan.pin'
        );
        $query->leftjoinSub($presensi, 'presensi', function ($join) {
            $join->on('karyawan.nik', '=', 'presensi.nik');
        });
        $query->orderBy('nama_karyawan');
        $karyawan = $query->paginate(10);
        $karyawan->appends(request()->all());
        $cabang = Cabang::orderBy('kode_cabang')->get();
        $data['karyawan'] = $karyawan;
        $data['cabang'] = $cabang;
        $data['denda_list'] = Denda::all()->toArray();
        return view('presensi.index', $data);
    }
    public function create($kode_jam_kerja = null)
    {

        //Get Data Karyawan By User
        //Get Data Karyawan By User
        $user = User::where('id', auth()->user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();

        //Cek Lokasi Kantor
        $lokasi_kantor = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();

        //Cek Lintas Hari
        $hariini = date("Y-m-d");
        $jamsekarang = date("H:i");
        $tgl_sebelumnya = date('Y-m-d', strtotime("-1 days", strtotime($hariini)));
        $cekpresensi_sebelumnya = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('tanggal', $tgl_sebelumnya)
            ->where('nik', $karyawan->nik)
            ->first();

        $ceklintashari_presensi = $cekpresensi_sebelumnya != null  ? $cekpresensi_sebelumnya->lintashari : 0;

        if ($ceklintashari_presensi == 1) {
            if ($jamsekarang < "08:00") {
                $hariini = $tgl_sebelumnya;
            }
        }

        $namahari = getnamaHari(date('D', strtotime($hariini)));

        $kode_dept = $karyawan->kode_dept;

        //Cek Presensi
        $presensi = Presensi::where('nik', $karyawan->nik)->where('tanggal', $hariini)->first();


        if ($kode_jam_kerja == null) {
            //Cek Jam Kerja By Date
            $jamkerja = Setjamkerjabydate::join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('nik', $karyawan->nik)
                ->where('tanggal', $hariini)
                ->first();

            //Jika Tidak Memiliki Jam Kerja By Date
            if ($jamkerja == null) {
                //Cek Jam Kerja harian / Jam Kerja Khusus / Jam Kerja Per Orangannya
                $jamkerja = Setjamkerjabyday::join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                    ->where('nik', $karyawan->nik)->where('hari', $namahari)->first();

                // Jika Jam Kerja Harian Kosong
                if ($jamkerja == null) {
                    $jamkerja = Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
                        ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                        ->where('kode_dept', $kode_dept)
                        ->where('kode_cabang', $karyawan->kode_cabang)
                        ->where('hari', $namahari)->first();
                }
            }
        } else {
            $jamkerja = Jamkerja::where('kode_jam_kerja', $kode_jam_kerja)->first();
        }


        $ceklibur = Detailharilibur::join('hari_libur', 'hari_libur_detail.kode_libur', '=', 'hari_libur.kode_libur')
            ->where('nik', $karyawan->nik)
            ->where('tanggal', $hariini)
            ->first();
        $data['harilibur'] = $ceklibur;

        if ($presensi != null && $presensi->status != 'h') {
            return view('presensi.notif_izin');
        } else if ($ceklibur != null) {
            return view('presensi.notif_libur', $data);
        } else if ($jamkerja == null) {
            return view('presensi.notif_jamkerja');
        }

        $data['hariini'] = $hariini;
        $data['jam_kerja'] = $jamkerja;
        $data['lokasi_kantor'] = $lokasi_kantor;
        $data['presensi'] = $presensi;
        $data['karyawan'] = $karyawan;
        $data['wajah'] = Facerecognition::where('nik', $karyawan->nik)->count();

        return view('presensi.create', $data);
    }

    public function store(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        $status_lock_location = $karyawan->lock_location;

        $status = $request->status;
        $lokasi = $request->lokasi;
        $kode_jam_kerja = $request->kode_jam_kerja;



        $tanggal_sekarang = date("Y-m-d");
        $jam_sekarang = date("H:i");

        $tanggal_kemarin = date("Y-m-d", strtotime("-1 days"));

        $tanggal_besok = date("Y-m-d", strtotime("+1 days"));

        //Cek Presensi Kemarin
        $presensi_kemarin = Presensi::where('nik', $karyawan->nik)
            ->join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal_kemarin)->first();

        $lintas_hari = $presensi_kemarin ? $presensi_kemarin->lintashari : 0;

        //Jika Presensi Kemarin Status Lintas Hari nya 1 Makan Tanggal Presensi Sekarang adalah Tanggal Kemarin
        $tanggal_presensi = $lintas_hari == 1 ? $tanggal_kemarin : $tanggal_sekarang;

        //Get Lokasi User
        $koordinat_user = explode(",", $lokasi);
        $latitude_user = $koordinat_user[0];
        $longitude_user = $koordinat_user[1];

        //Get Lokasi Kantor
        $cabang = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $lokasi_kantor = $cabang->lokasi_cabang;



        $koordinat_kantor = explode(",", $lokasi_kantor);
        $latitude_kantor = $koordinat_kantor[0];
        $longitude_kantor = $koordinat_kantor[1];

        $jarak = hitungjarak($latitude_kantor, $longitude_kantor, $latitude_user, $longitude_user);


        $radius = round($jarak["meters"]);

        $tanggal_pulang = $lintas_hari == 1 ? $tanggal_besok : $tanggal_sekarang;

        $in_out = $status == 1 ? "in" : "out";
        $image = $request->image;
        $folderPath = "public/uploads/absensi/";
        $formatName = $karyawan->nik . "-" . $tanggal_presensi . "-" . $in_out;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png";
        $file = $folderPath . $fileName;

        $jam_kerja = Jamkerja::where('kode_jam_kerja', $kode_jam_kerja)->first();

        $jam_presensi = $tanggal_sekarang . " " . $jam_sekarang;



        $jam_masuk = $tanggal_presensi . " " . date('H:i', strtotime($jam_kerja->jam_masuk));
        //Jam Mulai Absen adalah 60 Menit Sebelum Jam Masuk
        $jam_mulai_masuk = $tanggal_presensi . " " . date('H:i', strtotime('-60 minutes', strtotime($jam_masuk)));

        //Jamulai Absen Pulang adalah 1 Jam dari Jam Masuk
        $jam_mulai_pulang = $tanggal_presensi . " " . date('H:i', strtotime('+60 minutes', strtotime($jam_masuk)));

        $jam_pulang = $tanggal_pulang . " " . $jam_kerja->jam_pulang;


        //dd($jam_presensi . " " . $jam_mulai_pulang);
        //Cek Radius
        //dd($jam_presensi . " " . $jam_mulai_masuk);
        $presensi_hariini = Presensi::where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal_presensi)
            ->first();
        if ($status_lock_location == 1 && $radius > $cabang->radius_cabang) {
            return response()->json(['status' => false, 'message' => 'Anda Berada Di Luar Radius Kantor, Jarak Anda ' . formatAngka($radius) . ' Meters Dari Kantor', 'notifikasi' => 'notifikasi_radius'], 400);
        } else {
            if ($status == 1) {
                if ($presensi_hariini && $presensi_hariini->jam_in != null) {
                    return response()->json(['status' => false, 'message' => 'Anda Sudah Absen Masuk Hari Ini', 'notifikasi' => 'notifikasi_sudahabsen'], 400);
                } else if ($jam_presensi < $jam_mulai_masuk) {
                    return response()->json(['status' => false, 'message' => 'Maaf Belum Waktunya Absen Masuk, Waktu Absen Dimulai Pukul ' . formatIndo3($jam_mulai_masuk), 'notifikasi' => 'notifikasi_mulaiabsen'], 400);
                } else if ($jam_presensi > $jam_mulai_pulang) {
                    return response()->json(['status' => false, 'message' => 'Maaf Waktu Absen Masuk Sudah Habis ', 'notifikasi' => 'notifikasi_akhirabsen'], 400);
                } else {
                    try {
                        if ($presensi_hariini != null) {
                            Presensi::where('id', $presensi_hariini->id)->update([
                                'jam_in' => $jam_presensi,
                                'lokasi_in' => $lokasi,
                                'foto_in' => $fileName
                            ]);
                        } else {
                            Presensi::create([
                                'nik' => $karyawan->nik,
                                'tanggal' => $tanggal_presensi,
                                'jam_in' => $jam_presensi,
                                'jam_out' => null,
                                'lokasi_in' => $lokasi,
                                'lokasi_out' => null,
                                'foto_in' => $fileName,
                                'foto_out' => null,
                                'kode_jam_kerja' => $kode_jam_kerja,
                                'status' => 'h'
                            ]);
                            Storage::put($file, $image_base64);
                        }


                        return response()->json(['status' => true, 'message' => 'Berhasil Absen Masuk', 'notifikasi' => 'notifikasi_absenmasuk'], 200);
                    } catch (\Exception $e) {
                        return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
                    }
                }
            } else {
                if ($presensi_hariini && $presensi_hariini->jam_out != null) {
                    return response()->json(['status' => false, 'message' => 'Anda Sudah Absen Pulang Hari Ini', 'notifikasi' => 'notifikasi_sudahabsen'], 400);
                } else if ($jam_presensi < $jam_mulai_pulang) {
                    return response()->json(['status' => false, 'message' => 'Maaf Belum Waktunya Absen Pulang, Waktu Absen Dimulai Pukul ' . formatIndo3($jam_mulai_pulang), 'notifikasi' => 'notifikasi_mulaiabsen'], 400);
                } else {
                    try {
                        if ($presensi_hariini != null) {
                            Presensi::where('id', $presensi_hariini->id)->update([
                                'jam_out' => $jam_presensi,
                                'lokasi_out' => $lokasi,
                                'foto_out' => $fileName
                            ]);
                        } else {
                            Presensi::create([
                                'nik' => $karyawan->nik,
                                'tanggal' => $tanggal_presensi,
                                'jam_in' => null,
                                'jam_out' => $jam_presensi,
                                'lokasi_in' => null,
                                'lokasi_out' => $lokasi,
                                'foto_in' => null,
                                'foto_out' => $fileName,
                                'kode_jam_kerja' => $kode_jam_kerja,
                                'status' => 'h'
                            ]);
                            Storage::put($file, $image_base64);
                        }


                        return response()->json(['status' => true, 'message' => 'Berhasil Absen Pulang', 'notifikasi' => 'notifikasi_absenpulang'], 200);
                    } catch (\Exception $e) {
                        return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
                    }
                }
            }
        }
    }

    public function edit(Request $request)
    {
        $nik = Crypt::decrypt($request->nik);
        $tanggal = $request->tanggal;

        $karyawan = Karyawan::where('nik', $nik)->first();
        $jam_kerja = Jamkerja::all();
        $presensi = Presensi::where('nik', $nik)->where('tanggal', $tanggal)->first();
        $data['presensi'] = $presensi;
        $data['karyawan'] = $karyawan;
        $data['jam_kerja'] = $jam_kerja;
        $data['tanggal'] = $tanggal;

        return view('presensi.edit', $data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'tanggal' => 'required',
            'kode_jam_kerja' => 'required',
            'status' => 'required',
        ]);

        $nik = Crypt::decrypt($request->nik);
        $tanggal = $request->tanggal;
        $kode_jam_kerja = $request->kode_jam_kerja;
        $jam_in = $request->jam_in;
        $jam_out = $request->jam_out;
        $status = $request->status;

        try {
            $cekpresensi = Presensi::where('nik', $nik)->where('tanggal', $tanggal)->first();
            if (!empty($cekpresensi)) {
                Presensi::where('nik', $nik)->where('tanggal', $tanggal)->update([
                    'jam_in' => $jam_in,
                    'jam_out' => $jam_out,
                    'status' => $status,
                    'kode_jam_kerja' => $kode_jam_kerja,
                ]);
            } else {
                Presensi::create([
                    'nik' => $nik,
                    'tanggal' => $tanggal,
                    'jam_in' => $jam_in,
                    'jam_out' => $jam_out,
                    'kode_jam_kerja' => $kode_jam_kerja,
                    'status' => $status
                ]);
            }

            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }


    public function show($id, $status)
    {
        $presensi = Presensi::where('id', $id)
            ->join('karyawan', 'presensi.nik', '=', 'karyawan.nik')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        $cabang = Cabang::where('kode_cabang', $presensi->kode_cabang)->first();
        $lokasi = explode(',', $cabang->lokasi_cabang);
        $data['latitude'] = $lokasi[0];
        $data['longitude'] = $lokasi[1];
        // if (!empty($presensi->lokasi_cabang)) {
        //     $lokasi = explode(',', $presensi->lokasi_cabang);
        //     $data['latitude'] = $lokasi[0];
        //     $data['longitude'] = $lokasi[1];
        // } else {
        //     $data['latitude'] = $cabang->latitude_cabang;
        //     $data['longitude'] = $cabang->longitude_cabang;
        // }
        $data['presensi'] = $presensi;
        $data['status'] = $status;
        $data['cabang'] = $cabang;

        return view('presensi.show', $data);
    }

    public function histori(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', auth()->user()->id)->first();
        $data['datapresensi'] = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('presensi.nik', $userkaryawan->nik)
            ->leftJoin('presensi_izinabsen_approve', 'presensi.id', '=', 'presensi_izinabsen_approve.id_presensi')
            ->leftJoin('presensi_izinabsen', 'presensi_izinabsen_approve.kode_izin', '=', 'presensi_izinabsen.kode_izin')

            ->leftJoin('presensi_izinsakit_approve', 'presensi.id', '=', 'presensi_izinsakit_approve.id_presensi')
            ->leftJoin('presensi_izinsakit', 'presensi_izinsakit_approve.kode_izin_sakit', '=', 'presensi_izinsakit.kode_izin_sakit')

            ->leftJoin('presensi_izincuti_approve', 'presensi.id', '=', 'presensi_izincuti_approve.id_presensi')
            ->leftJoin('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->select(
                'presensi.*',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.lintashari',
                'presensi_izinabsen.keterangan as keterangan_izin',
                'presensi_izinsakit.keterangan as keterangan_izin_sakit',
                'presensi_izincuti.keterangan as keterangan_izin_cuti'
            )
            ->when(!empty($request->dari) && !empty($request->sampai), function ($q) use ($request) {
                $q->whereBetween('presensi.tanggal', [$request->dari, $request->sampai]);
            })
            ->orderBy('presensi.tanggal', 'desc')
            ->limit(30)
            ->get();
        return view('presensi.histori', $data);
    }
}
