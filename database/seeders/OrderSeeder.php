<?php

namespace Database\Seeders;

use App\Models\Distributor;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $distributors = Distributor::all();

        foreach ($distributors as $distributor) {
            for ($i = 0; $i < rand(3, 6); $i++) {
                Order::create([
                    'distributor_id' => $distributor->id,
                    'total_amount' => rand(1_000_000, 100_000_000),
                    'ordered_at' => Carbon::now()->subDays(rand(0, 90)),
                ]);
            }
        }
    }
}
