<?php

namespace App\Http\Controllers;

use App\Models\Facerecognition;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class FacerecognitionController extends Controller
{
    public function create($nik)
    {
        $data['nik'] = Crypt::decrypt($nik);
        return view('facerecognition.create', $data);
    }

    public function store(Request $request)
    {
        $image = $request->image;
        $karyawan = Karyawan::where('nik', $request->nik)->first();
        $nama_folder = $karyawan->nik . "-" . getNamaDepan($karyawan->nama_karyawan);
        $folderPath = "public/uploads/facerecognition/" . $request->nik . "-" . getNamaDepan(strtolower($karyawan->nama_karyawan)) . "/";

        // Membuat folder jika belum ada dan set permission
        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
            Storage::setVisibility($folderPath, 'public');
            chmod(storage_path('app/' . $folderPath), 0775);
        }

        $cekWajah = Facerecognition::where('nik', $request->nik)->count();
        // $formatName = $karyawan->nik . "-" . $tanggal_presensi . "-" . $in_out;
        $formatName = $cekWajah + 1;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png";
        $file = $folderPath . $fileName;

        try {
            Facerecognition::create([
                'nik' => $request->nik,
                'wajah' => $fileName
            ]);
            Storage::put($file, $image_base64);
            return response()->json(['success' => true, 'message' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $facerecognition = Facerecognition::where('id', $id)->firstorfail();
        $karyawan = Karyawan::where('nik', $facerecognition->nik)->first();
        try {
            $nama_file = $facerecognition->wajah;
            $nama_folder = $karyawan->nik . "-" . getNamaDepan(strtolower($karyawan->nama_karyawan));
            $path = 'public/uploads/facerecognition/' . $nama_folder . "/" . $nama_file;
            Storage::delete($path);
            $facerecognition->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function getWajah()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $wajah = Facerecognition::where('nik', $userkaryawan->nik)->get();
        return response()->json($wajah);
    }
}
