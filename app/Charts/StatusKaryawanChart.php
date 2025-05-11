<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class StatusKaryawanChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\PieChart
    {
        // Ambil jumlah karyawan berdasarkan status (T, K, O)
        $rawData = DB::table('karyawan')
            ->select('status_karyawan', DB::raw('count(*) as total'))
            ->groupBy('status_karyawan')
            ->pluck('total', 'status_karyawan')
            ->toArray();

        // Mapping status singkatan ke nama lengkap
        $statusLabels = [
            'T' => 'Tetap',
            'K' => 'Kontrak',
            'O' => 'Outsourcing'
        ];

        // Konversi kode status ke label lengkap
        $labels = [];
        $data = [];

        foreach ($statusLabels as $key => $label) {
            $labels[] = $label;
            $data[] = $rawData[$key] ?? 0; // Jika tidak ada data, set 0
        }
        return $this->chart->pieChart()
            // ->setTitle('Data Karyawan.')
            // ->setSubtitle('Berdasarkan Status Karyawan')
            ->addData($data)
            ->setLabels($labels)
            ->setColors(['#FF6384', '#36A2EB', '#FFCE56'])
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
