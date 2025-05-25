<?php

use App\Http\Controllers\CabangController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DendaController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\FacerecognitionController;
use App\Http\Controllers\GeneralsettingController;
use App\Http\Controllers\HariliburController;
use App\Http\Controllers\IzinabsenController;
use App\Http\Controllers\IzincutiController;
use App\Http\Controllers\IzinsakitController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\JamkerjabydeptController;
use App\Http\Controllers\JamkerjaController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengajuanizinController;
use App\Http\Controllers\Permission_groupController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WagatewayController;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('auth.loginuser');
    })->name('loginuser');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Setings
    //Role

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile.index');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard.index');
    });
    Route::middleware('role:super admin')->controller(RoleController::class)->group(function () {
        Route::get('/roles', 'index')->name('roles.index');
        Route::get('/roles/create', 'create')->name('roles.create');
        Route::post('/roles', 'store')->name('roles.store');
        Route::get('/roles/{id}/edit', 'edit')->name('roles.edit');
        Route::put('/roles/{id}/update', 'update')->name('roles.update');
        Route::delete('/roles/{id}/delete', 'destroy')->name('roles.delete');
        Route::get('/roles/{id}/createrolepermission', 'createrolepermission')->name('roles.createrolepermission');
        Route::post('/roles/{id}/storerolepermission', 'storerolepermission')->name('roles.storerolepermission');
    });


    Route::middleware('role:super admin')->controller(Permission_groupController::class)->group(function () {
        Route::get('/permissiongroups', 'index')->name('permissiongroups.index');
        Route::get('/permissiongroups/create', 'create')->name('permissiongroups.create');
        Route::post('/permissiongroups', 'store')->name('permissiongroups.store');
        Route::get('/permissiongroups/{id}/edit', 'edit')->name('permissiongroups.edit');
        Route::put('/permissiongroups/{id}/update', 'update')->name('permissiongroups.update');
        Route::delete('/permissiongroups/{id}/delete', 'destroy')->name('permissiongroups.delete');
    });


    Route::middleware('role:super admin')->controller(PermissionController::class)->group(function () {
        Route::get('/permissions', 'index')->name('permissions.index');
        Route::get('/permissions/create', 'create')->name('permissions.create');
        Route::post('/permissions', 'store')->name('permissions.store');
        Route::get('/permissions/{id}/edit', 'edit')->name('permissions.edit');
        Route::put('/permissions/{id}/update', 'update')->name('permissions.update');
        Route::delete('/permissions/{id}/delete', 'destroy')->name('permissions.delete');
    });

    Route::middleware('role:super admin')->controller(UserController::class)->group(function () {
        Route::get('/users', 'index')->name('users.index');
        Route::get('/users/create', 'create')->name('users.create');
        Route::post('/users', 'store')->name('users.store');
        Route::get('/users/{id}/edit', 'edit')->name('users.edit');
        Route::put('/users/{id}/update', 'update')->name('users.update');
        Route::delete('/users/{id}/delete', 'destroy')->name('users.delete');

        Route::get('/users/{id}/editpassword', 'editpassword')->name('users.editpassword');
        Route::put('/users/{id}/updatepassword', 'updatepassword')->name('users.updatepassword');
    });

    //Data Master
    //Dat Karyawan
    Route::controller(KaryawanController::class)->group(function () {
        Route::get('/karyawan', 'index')->name('karyawan.index')->can('karyawan.index');
        Route::get('/karyawan/create', 'create')->name('karyawan.create')->can('karyawan.create');
        Route::post('/karyawan', 'store')->name('karyawan.store')->can('karyawan.create');
        Route::get('/karyawan/import', 'import')->name('karyawan.import')->can('karyawan.create');
        Route::get('/karyawan/download-template', 'download_template')->name('karyawan.download_template')->can('karyawan.create');
        Route::post('/karyawan/import', 'import_proses')->name('karyawan.import_proses')->can('karyawan.create');
        Route::get('/karyawan/{nik}/edit', 'edit')->name('karyawan.edit')->can('karyawan.edit');
        Route::put('/karyawan/{nik}', 'update')->name('karyawan.update')->can('karyawan.edit');
        Route::delete('/karyawan/{nik}', 'destroy')->name('karyawan.delete')->can('karyawan.delete');
        Route::get('/karyawan/{nik}/show', 'show')->name('karyawan.show')->can('karyawan.show');
        Route::get('/karyawan/{nik}/setjamkerja', 'setjamkerja')->name('karyawan.setjamkerja')->can('karyawan.setjamkerja');
        Route::post('/karyawan/{nik}/storejamkerjabyday', 'storejamkerjabyday')->name('karyawan.storejamkerjabyday')->can('karyawan.setjamkerja');
        Route::post('/karyawan/storejamkerjabydate', 'storejamkerjabydate')->name('karyawan.storejamkerjabydate')->can('karyawan.setjamkerja');

        Route::post('/karyawan/getjamkerjabydate', 'getjamkerjabydate')->name('karyawan.getjamkerjabydate')->can('karyawan.setjamkerja');
        Route::post('/karyawan/deletejamkerjabydate', 'deletejamkerjabydate')->name('karyawan.deletejamkerjabydate')->can('karyawan.setjamkerja');

        Route::get('/karyawan/{nik}/createuser', 'createuser')->name('karyawan.createuser')->can('users.create');
        Route::get('/karyawan/{nik}/deleteuser', 'deleteuser')->name('karyawan.deleteuser')->can('users.create');
        Route::get('/karyawan/{nik}/lockunlocklocation', 'lockunlocklocation')->name('karyawan.lockunlocklocation')->can('karyawan.edit');
        Route::get('/karyawan/{nik}/lockunlockjamkerja', 'lockunlockjamkerja')->name('karyawan.lockunlockjamkerja')->can('karyawan.edit');
        Route::get('/karyawan/{nik}/idcard', 'idcard')->name('karyawan.idcard');

        Route::get('/karyawan/getkaryawan', 'getkaryawan')->name('karyawan.getkaryawan');
    });

    Route::controller(DepartemenController::class)->group(function () {
        Route::get('/departemen', 'index')->name('departemen.index')->can('departemen.index');
        Route::get('/departemen/create', 'create')->name('departemen.create')->can('departemen.create');
        Route::post('/departemen', 'store')->name('departemen.store')->can('departemen.create');
        Route::get('/departemen/{nik}', 'edit')->name('departemen.edit')->can('departemen.edit');
        Route::put('/departemen/{nik}', 'update')->name('departemen.update')->can('departemen.edit');
        Route::delete('/departemen/{nik}/delete', 'destroy')->name('departemen.delete')->can('departemen.delete');
    });

    Route::controller(JabatanController::class)->group(function () {
        Route::get('/jabatan', 'index')->name('jabatan.index')->can('jabatan.index');
        Route::get('/jabatan/create', 'create')->name('jabatan.create')->can('jabatan.create');
        Route::post('/jabatan', 'store')->name('jabatan.store')->can('jabatan.create');
        Route::get('/jabatan/{kode_jabatan}', 'edit')->name('jabatan.edit')->can('jabatan.edit');
        Route::put('/jabatan/{kode_jabatan}', 'update')->name('jabatan.update')->can('jabatan.edit');
        Route::delete('/jabatan/{kode_jabatan}/delete', 'destroy')->name('jabatan.delete')->can('jabatan.delete');
    });


    Route::controller(CabangController::class)->group(function () {
        Route::get('/cabang', 'index')->name('cabang.index')->can('cabang.index');
        Route::get('/cabang/create', 'create')->name('cabang.create')->can('cabang.create');
        Route::post('/cabang', 'store')->name('cabang.store')->can('cabang.create');
        Route::get('/cabang/{kode_cabang}', 'edit')->name('cabang.edit')->can('cabang.edit');
        Route::put('/cabang/{kode_cabang}', 'update')->name('cabang.update')->can('cabang.edit');
        Route::delete('/cabang/{kode_cabang}/delete', 'destroy')->name('cabang.delete')->can('cabang.delete');
    });

    Route::controller(CutiController::class)->group(function () {
        Route::get('/cuti', 'index')->name('cuti.index')->can('cuti.index');
        Route::get('/cuti/create', 'create')->name('cuti.create')->can('cuti.create');
        Route::post('/cuti', 'store')->name('cuti.store')->can('cuti.create');
        Route::get('/cuti/{kode_cuti}', 'edit')->name('cuti.edit')->can('cuti.edit');
        Route::put('/cuti/{kode_cuti}', 'update')->name('cuti.update')->can('cuti.edit');
        Route::delete('/cuti/{kode_cuti}/delete', 'destroy')->name('cuti.delete')->can('cuti.delete');
    });

    Route::controller(JamkerjaController::class)->group(function () {
        Route::get('/jamkerja', 'index')->name('jamkerja.index')->can('jamkerja.index');
        Route::get('/jamkerja/create', 'create')->name('jamkerja.create')->can('jamkerja.create');
        Route::post('/jamkerja', 'store')->name('jamkerja.store')->can('jamkerja.create');
        Route::get('/jamkerja/{kode_jam_kerja}/edit', 'edit')->name('jamkerja.edit')->can('jamkerja.edit');
        Route::put('/jamkerja/{kode_jam_kerja}/update', 'update')->name('jamkerja.update')->can('jamkerja.edit');
        Route::delete('/jamkerja/{kode_jam_kerja}/delete', 'destroy')->name('jamkerja.delete')->can('jamkerja.delete');
    });

    Route::controller(HariliburController::class)->group(function () {
        Route::get('/harilibur', 'index')->name('harilibur.index')->can('harilibur.index');
        Route::get('/harilibur/create', 'create')->name('harilibur.create')->can('harilibur.create');
        Route::post('/harilibur', 'store')->name('harilibur.store')->can('harilibur.create');
        Route::get('/harilibur/{kode_libur}/edit', 'edit')->name('harilibur.edit')->can('harilibur.edit');
        Route::put('/harilibur/{kode_libur}', 'update')->name('harilibur.update')->can('harilibur.edit');
        Route::delete('/harilibur/{kode_libur}/delete', 'destroy')->name('harilibur.delete')->can('harilibur.delete');
        Route::get('/harilibur/{kode_libur}/aturharilibur', 'aturharilibur')->name('harilibur.aturharilibur')->can('harilibur.setharilibur');
        Route::get('/harilibur/{kode_libur}/getkaryawanlibur', 'getkaryawanlibur')->name('harilibur.getkaryawanlibur');
        Route::get('/harilibur/{kode_libur}/aturkaryawan', 'aturkaryawan')->name('harilibur.aturkaryawan');
        Route::post('/harilibur/getkaryawan', 'getkaryawan')->name('harilibur.getkaryawan');
        Route::post('/harilibur/updateliburkaryawan', 'updateliburkaryawan')->name('harilibur.updateliburkaryawan');
        Route::post('/harilibur/deletekaryawanlibur', 'deletekaryawanlibur')->name('harilibur.deletekaryawanlibur');
        Route::post('/harilibur/tambahkansemua', 'tambahkansemua')->name('harilibur.tambahkansemua');
        Route::post('/harilibur/batalkansemua', 'batalkansemua')->name('harilibur.batalkansemua');
    });

    Route::controller(PresensiController::class)->group(function () {
        Route::get('/presensi', 'index')->name('presensi.index')->can('presensi.index');
        Route::get('/presensi/histori', 'histori')->name('presensi.histori')->can('presensi.index');
        Route::get('/presensi/create', 'create')->name('presensi.create')->can('presensi.create');
        Route::post('/presensi', 'store')->name('presensi.store')->can('presensi.create');
        Route::post('/presensi/edit', 'edit')->name('presensi.edit')->can('presensi.edit');
        Route::post('/presensi/update', 'update')->name('presensi.update')->can('presensi.edit');
        Route::delete('/presensi/{id}/delete', 'destroy')->name('presensi.delete')->can('presensi.delete');
        Route::get('/presensi/{id}/{status}/show', 'show')->name('presensi.show');
        Route::post('/presensi/edit', 'edit')->name('presensi.edit')->can('presensi.edit');

        Route::post('/presensi/getdatamesin', 'getdatamesin')->name('presensi.getdatamesin');
        Route::post('/presensi/{pin}/{status_scan}/updatefrommachine', 'updatefrommachine')->name('presensi.updatefrommachine');
    });

    Route::controller(JamkerjabydeptController::class)->group(function () {
        Route::get('/jamkerjabydept', 'index')->name('jamkerjabydept.index')->can('jamkerjabydept.index');
        Route::get('/jamkerjabydept/create', 'create')->name('jamkerjabydept.create')->can('jamkerjabydept.create');
        Route::post('/jamkerjabydept', 'store')->name('jamkerjabydept.store')->can('jamkerjabydept.create');
        Route::get('/jamkerjabydept/{kode_jk_dept}/edit', 'edit')->name('jamkerjabydept.edit')->can('jamkerjabydept.edit');
        Route::put('/jamkerjabydept/{kode_jk_dept}', 'update')->name('jamkerjabydept.update')->can('jamkerjabydept.edit');
        Route::delete('/jamkerjabydept/{kode_jk_dept}/delete', 'destroy')->name('jamkerjabydept.delete')->can('jamkerjabydept.delete');
    });

    Route::controller(IzinabsenController::class)->group(function () {
        Route::get('/izinabsen', 'index')->name('izinabsen.index')->can('izinabsen.index');
        Route::get('/izinabsen/create', 'create')->name('izinabsen.create')->can('izinabsen.create');
        Route::post('/izinabsen', 'store')->name('izinabsen.store')->can('izinabsen.create');
        Route::get('/izinabsen/{kode_izin}/approve', 'approve')->name('izinabsen.approve')->can('izinabsen.approve');
        Route::delete('/izinabsen/{kode_izin}/cancelapprove', 'cancelapprove')->name('izinabsen.cancelapprove')->can('izinabsen.approve');
        Route::post('/izinabsen/{kode_izin}/storeapprove', 'storeapprove')->name('izinabsen.storeapprove')->can('izinabsen.approve');
        Route::get('/izinabsen/{id}/edit', 'edit')->name('izinabsen.edit')->can('izinabsen.edit');
        Route::put('/izinabsen/{id}', 'update')->name('izinabsen.update')->can('izinabsen.edit');
        Route::get('/izinabsen/{kode_izin}/show', 'show')->name('izinabsen.show')->can('izinabsen.index');
        Route::delete('/izinabsen/{id}/delete', 'destroy')->name('izinabsen.delete')->can('izinabsen.delete');
    });

    Route::controller(IzinsakitController::class)->group(function () {
        Route::get('/izinsakit', 'index')->name('izinsakit.index')->can('izinsakit.index');
        Route::get('/izinsakit/create', 'create')->name('izinsakit.create')->can('izinsakit.create');
        Route::post('/izinsakit', 'store')->name('izinsakit.store')->can('izinsakit.create');
        Route::get('/izinsakit/{kode_izin_sakit}/edit', 'edit')->name('izinsakit.edit')->can('izinsakit.edit');
        Route::put('/izinsakit/{kode_izin_sakit}', 'update')->name('izinsakit.update')->can('izinsakit.edit');
        Route::get('/izinsakit/{kode_izin_sakit}/show', 'show')->name('izinsakit.show')->can('izinsakit.index');
        Route::delete('/izinsakit/{kode_izin_sakit}/delete', 'destroy')->name('izinsakit.delete')->can('izinsakit.delete');

        Route::get('/izinsakit/{kode_izin_sakit}/approve', 'approve')->name('izinsakit.approve')->can('izinsakit.approve');
        Route::delete('/izinsakit/{kode_izin_sakit}/cancelapprove', 'cancelapprove')->name('izinsakit.cancelapprove')->can('izinsakit.approve');
        Route::post('/izinsakit/{kode_izin_sakit}/storeapprove', 'storeapprove')->name('izinsakit.storeapprove')->can('izinsakit.approve');
    });


    Route::controller(IzincutiController::class)->group(function () {
        Route::get('/izincuti', 'index')->name('izincuti.index')->can('izincuti.index');
        Route::get('/izincuti/create', 'create')->name('izincuti.create')->can('izincuti.create');
        Route::post('/izincuti', 'store')->name('izincuti.store')->can('izincuti.create');
        Route::get('/izincuti/{kode_izin_cuti}/edit', 'edit')->name('izincuti.edit')->can('izincuti.edit');
        Route::put('/izincuti/{kode_izin_cuti}', 'update')->name('izincuti.update')->can('izincuti.edit');
        Route::get('/izincuti/{kode_izin_cuti}/show', 'show')->name('izincuti.show')->can('izincuti.index');
        Route::delete('/izincuti/{kode_izin_cuti}/delete', 'destroy')->name('izincuti.delete')->can('izincuti.delete');

        Route::get('/izincuti/{kode_izin_cuti}/approve', 'approve')->name('izincuti.approve')->can('izincuti.approve');
        Route::delete('/izincuti/{kode_izin_cuti}/cancelapprove', 'cancelapprove')->name('izincuti.cancelapprove')->can('izincuti.approve');
        Route::post('/izincuti/{kode_izin_cuti}/storeapprove', 'storeapprove')->name('izincuti.storeapprove')->can('izincuti.approve');
    });

    Route::controller(PengajuanizinController::class)->group(function () {
        Route::get('/pengajuanizin', 'index')->name('pengajuanizin.index');
    });


    Route::controller(GeneralsettingController::class)->group(function () {
        Route::get('/generalsetting', 'index')->name('generalsetting.index')->can('generalsetting.index');
        Route::put('/generalsetting/{id}', 'update')->name('generalsetting.update')->can('generalsetting.edit');
    });

    Route::controller(DendaController::class)->group(function () {
        Route::get('/denda', 'index')->name('denda.index')->can('generalsetting.index');
        Route::get('/denda/create', 'create')->name('denda.create')->can('generalsetting.index');
        Route::post('/denda', 'store')->name('denda.store')->can('generalsetting.index');
        Route::get('/denda/{id}/edit', 'edit')->name('denda.edit')->can('generalsetting.index');
        Route::put('/denda/{id}', 'update')->name('denda.update')->can('generalsetting.index');
        Route::delete('/denda/{id}/delete', 'destroy')->name('denda.delete')->can('generalsetting.index');
    });

    Route::controller(LaporanController::class)->group(function () {
        Route::get('/laporan/presensi', 'presensi')->name('laporan.presensi')->can('laporan.presensi');
        Route::post('/laporan/cetakpresensi', 'cetakpresensi')->name('laporan.cetakpresensi')->can('laporan.presensi');
    });

    Route::controller(FacerecognitionController::class)->group(function () {
        Route::get('/facerecognition/{nik}/create', 'create')->name('facerecognition.create');
        Route::post('/facerecognition/store', 'store')->name('facerecognition.store');
        Route::delete('/facerecognition/{id}/delete', 'destroy')->name('facerecognition.delete');

        Route::get('/facerecognition/getwajah', 'getWajah')->name('facerecognition.getwajah');
    });

    Route::controller(WagatewayController::class)->group(function () {
        Route::get('/wagateway', 'index')->name('wagateway.index')->can('wagateway.index');
    });
});


Route::get('/createrolepermission', function () {

    try {
        Role::create(['name' => 'super admin']);
        // Permission::create(['name' => 'view-karyawan']);
        // Permission::create(['name' => 'view-departemen']);
        echo "Sukses";
    } catch (\Exception $e) {
        echo "Error";
    }
});

require __DIR__ . '/auth.php';
