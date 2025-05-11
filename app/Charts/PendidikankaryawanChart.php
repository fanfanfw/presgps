<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class PendidikankaryawanChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\BarChart
    {
        // Ambil jumlah karyawan berdasarkan pendidikan_terakhir
        $rawData = DB::table('karyawan')
            ->select('pendidikan_terakhir', DB::raw('count(*) as total'))
            ->groupBy('pendidikan_terakhir')
            ->pluck('total', 'pendidikan_terakhir')
            ->toArray();

        // Mapping pendidikan_terakhir ke label lengkap
        $pendidikanLabels = [
            'SD' => 'SD',
            'SMP' => 'SMP',
            'SMA' => 'SMA',
            'SMK' => 'SMK',
            'D1' => 'D1',
            'D2' => 'D2',
            'D3' => 'D3',
            'D4' => 'D4',
            'S1' => 'S1',
            'S2' => 'S2',
            'S3' => 'S3'
        ];

        // Konversi kode pendidikan_terakhir ke label lengkap
        $labels = [];
        $data = [];

        foreach ($pendidikanLabels as $key => $label) {
            $labels[] = $label;
            $data[] = $rawData[$key] ?? 0; // Jika tidak ada data, set 0
        }
        return $this->chart->barChart()
            // ->setTitle('Distribusi Pendidikan Karyawan')
            // ->setSubtitle('Berdasarkan Tingkat Pendidikan')
            ->addData('Jumlah Karyawan', array_map('intval', $data))
            ->setHeight(328)
            ->setXAxis($labels);
    }
}
