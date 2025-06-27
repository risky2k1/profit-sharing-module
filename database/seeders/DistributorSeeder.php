<?php

namespace Database\Seeders;

use App\Models\Distributor;
use App\Models\DistributorMonthlyStats;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistributorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Distributor::query()->delete();
        DistributorMonthlyStats::query()->delete();
        $month = now()->month;
        $year = now()->year;

        $distributors = [
            [
                'code' => 'NPP001',
                'name' => 'NPP Chính 1',
                'personal_sales' => 6000000,
                'children' => [
                    ['code' => 'NPP11', 'name' => 'NPP Con 1.1', 'personal_sales' => 300000000],
                    ['code' => 'NPP12', 'name' => 'NPP Con 1.2', 'personal_sales' => 300000000],
                ],
            ],
            [
                'code' => 'NPP002',
                'name' => 'NPP Chính 2',
                'personal_sales' => 7000000,
                'children' => [
                    ['code' => 'NPP21', 'name' => 'NPP Con 2.1', 'personal_sales' => 320000000],
                    ['code' => 'NPP22', 'name' => 'NPP Con 2.2', 'personal_sales' => 330000000],
                ],
            ],
            [
                'code' => 'NPP003',
                'name' => 'NPP Chính 3',
                'personal_sales' => 8000000,
                'children' => [
                    ['code' => 'NPP31', 'name' => 'NPP Con 3.1', 'personal_sales' => 400000000],
                    ['code' => 'NPP32', 'name' => 'NPP Con 3.2', 'personal_sales' => 300000000],
                ],
            ],
            [
                'code' => 'NPP004',
                'name' => 'NPP Chính 4',
                'personal_sales' => 2000000,
                'children' => [
                    ['code' => 'NPP41', 'name' => 'NPP Con 4.1', 'personal_sales' => 1000000],
                    ['code' => 'NPP42', 'name' => 'NPP Con 4.2', 'personal_sales' => 3000000],
                ],
            ],
            [
                'code' => 'NPP005',
                'name' => 'NPP Chính 5',
                'personal_sales' => 2500000,
                'children' => [],
            ],
        ];

        foreach ($distributors as $distributor) {
            $parent = Distributor::create([
                'code' => $distributor['code'],
                'name' => $distributor['name'],
            ]);

            for ($m = -2; $m <= 0; $m++) {
                $date = Carbon::now()->addMonths($m);
                DistributorMonthlyStats::create([
                    'distributor_id' => $parent->id,
                    'year' => $date->year,
                    'month' => $date->month,
                    'personal_sales' => $distributor['personal_sales'],
                ]);
            }

            foreach ($distributor['children'] as $childData) {
                $child = Distributor::create([
                    'code' => $childData['code'],
                    'name' => $childData['name'],
                    'parent_id' => $parent->id,
                ]);

                for ($m = -2; $m <= 0; $m++) {
                    $date = Carbon::now()->addMonths($m);
                    DistributorMonthlyStats::create([
                        'distributor_id' => $child->id,
                        'year' => $date->year,
                        'month' => $date->month,
                        'personal_sales' => $childData['personal_sales'],
                    ]);
                }
            }
        }
    }
}
