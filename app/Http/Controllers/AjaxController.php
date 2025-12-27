<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function hitungHariKerja(Request $request)
    {
        $request->validate([
            'dari' => 'required|date',
            'sampai' => 'required|date',
            'nik' => 'nullable|string',
        ]);

        $nik = $request->nik;
        if (empty($nik)) {
            $user = User::where('id', auth()->user()->id)->first();
            $userkaryawan = $user != null ? Userkaryawan::where('id_user', $user->id)->first() : null;
            $nik = $userkaryawan->nik ?? null;
        }

        if (empty($nik)) {
            return response()->json([
                'success' => false,
                'message' => 'NIK wajib diisi',
            ], 422);
        }

        $jmlHari = hitungHariKerja($nik, $request->dari, $request->sampai);

        return response()->json([
            'success' => true,
            'jml_hari' => $jmlHari,
        ]);
    }
}

