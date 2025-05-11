<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class JeniskelaminkaryawanChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\PieChart
    {
        // Ambil jumlah karyawan berdasarkan jenis_kelamin (L, P)
        $rawData = DB::table('karyawan')
            ->select('jenis_kelamin', DB::raw('count(*) as total'))
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin')
            ->toArray();

        // Mapping jenis_kelamin singkatan ke nama lengkap
        $jenisKelaminLabels = [
            'L' => 'Laki-Laki',
            'P' => 'Perempuan'
        ];

        // Konversi kode jenis_kelamin ke label lengkap
        $labels = [];
        $data = [];

        foreach ($jenisKelaminLabels as $key => $label) {
            $labels[] = $label;
            $data[] = $rawData[$key] ?? 0; // Jika tidak ada data, set 0
        }
        return $this->chart->pieChart()
            // ->setTitle('Data Karyawan.')
            // ->setSubtitle('Berdasarkan Jenis Kelamin')
            ->addData($data)
            ->setLabels($labels)
            ->setColors(['#FF6384', '#36A2EB'])
            ->setDataLabels(true)
            ->setOptions([
                'dataLabels' => [
                    'enabled' => true,
                    'formatter' => function ($val, $opts) {
                        return round($val, 1) . '%'; // Menampilkan dalam persen
                    },
                    'dropShadow' => [
                        'enabled' => true
                    ]
                ]
            ]);
    }
}
