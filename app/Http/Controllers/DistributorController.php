<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\MonthlyReward;
use Illuminate\Http\Request;

class DistributorController extends Controller
{
    public function index(Request $request)
    {
        $now = now();

        $monthRewards = MonthlyReward::query()->first();
        $distributors = Distributor::with([
            'children' => function ($query) use ($now) {
                $query->with(['monthlyStats' => function ($query) use ($now) {
                    $query->where('year', $now->year)->where('month', $now->month);
                }]);
            },
            'monthlyStats' => function ($query) use ($now) {
                $query->where('year', $now->year)->where('month', $now->month);
            },
        ])
            ->where('parent_id', null)
            ->get();

        return view('distributors.index', [
            'distributors' => $distributors,
            'reward' => $monthRewards
        ]);
    }
}
