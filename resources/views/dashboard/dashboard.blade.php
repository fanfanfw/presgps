@extends('layouts.app')
@section('titlepage', 'Dashboard')

@section('content')
@section('navigasi')
    <span>Dashboard</span>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card mb-6">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">

                                <div>
                                    <p class="mb-1">Data Karyawn Aktif</p>
                                    <h4 class="mb-1">{{ $status_karyawan->jml_aktif }}</h4>
                                </div>
                                <img src="{{ asset('assets/img/illustrations/karyawan1.png') }}" height="70" alt="view sales" class="me-3">
                            </div>

                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
                                <div>
                                    <p class="mb-1">Karyawan Tetap</p>
                                    <h4 class="mb-1">{{ $status_karyawan->jml_tetap }}</h4>
                                </div>
                                <img src="{{ asset('assets/img/illustrations/karyawan2.webp') }}" height="70" alt="view sales" class="me-3">
                            </div>

                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-start border-end pb-4 pb-sm-0 card-widget-3">
                                <div>
                                    <p class="mb-1">Karyawan Kontrak</p>
                                    <h4 class="mb-1">{{ $status_karyawan->jml_kontrak }}</h4>
                                </div>
                                <img src="{{ asset('assets/img/illustrations/karyawan3.png') }}" height="70" alt="view sales" class="me-3">
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="mb-1">Outsourcing</p>
                                    <h4 class="mb-1">{{ $status_karyawan->jml_outsourcing }}</h4>
                                </div>
                                <img src="{{ asset('assets/img/illustrations/karyawan4.webp') }}" height="70" alt="view sales" class="me-3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="row mt-3">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Status Karyawan</h4>
            </div>
            <div class="card-body">
                {!! $chart->container() !!}
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Jenis Kelamin</h4>
            </div>
            <div class="card-body">
                {!! $jkchart->container() !!}
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Pendidikan Karyawan</h4>
            </div>
            <div class="card-body">
                {!! $pddchart->container() !!}
            </div>
        </div>
    </div>
</div>
@endsection
@push('myscript')
<script src="{{ $chart->cdn() }}"></script>
{{ $chart->script() }}
{{ $jkchart->script() }}
{{ $pddchart->script() }}
@endpush
