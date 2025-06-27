<?php

namespace App\Console\Commands;

use App\Models\MonthlyReward;
use Illuminate\Console\Command;
use App\Models\Distributor;
use App\Models\DistributorMonthlyStats;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckQualifiedCommand extends Command
{
    protected $signature = 'rewards:check {year?} {month?}';

    protected $description = 'Kiểm tra điều kiện và cập nhật is_qualified cho các NPP trong tháng chỉ định hoặc tháng hiện tại';

    public function handle(): void
    {
        $now = now();
        $year = (int)($this->argument('year') ?? $now->year);
        $month = (int)($this->argument('month') ?? $now->month);

        $this->info("\n=== Đang kiểm tra điều kiện nhận thưởng cho tháng {$month}/{$year} ===\n");

        $distributors = Distributor::with(['children', 'monthlyStats'])
            ->whereNull('parent_id')
            ->get();

        $qualifiedCount = 0;
        $totalSystemSales = 0;

        foreach ($distributors as $distributor) {
            $qualified = $this->checkQualified($distributor, $year, $month);

            DistributorMonthlyStats::updateOrCreate(
                [
                    'distributor_id' => $distributor->id,
                    'year' => $year,
                    'month' => $month,
                ],
                ['is_qualified' => $qualified]
            );

            $status = $qualified ? '✅ ĐỦ điều kiện' : '❌ KHÔNG đủ';
            $this->line("- {$distributor->code} ({$distributor->name}): {$status}");

            if ($qualified) {
                $qualifiedCount++;
            }

            $totalSystemSales += $distributor->totalBranchSale($year, $month);
        }

        $rewardPool = round($totalSystemSales * 0.01);

        MonthlyReward::updateOrCreate(
            ['year' => $year, 'month' => $month],
            [
                'total_sales' => $totalSystemSales,
                'reward_pool' => $rewardPool,
                'qualified_count' => $qualifiedCount,
                'reward_per_distributor' => $qualifiedCount > 0 ? intdiv($rewardPool, $qualifiedCount) : 0,
            ]
        );

        $this->info("\nTổng doanh số hệ thống: " . number_format($totalSystemSales) . " VND");
        $this->info("Quỹ thưởng 1%: " . number_format($rewardPool) . " VND");
        $this->info("Tổng NPP đủ điều kiện: {$qualifiedCount}");

        if ($qualifiedCount > 0) {
            $rewardPerDistributor = intdiv($rewardPool, $qualifiedCount);
            $this->info("Mỗi NPP đủ điều kiện nhận: " . number_format($rewardPerDistributor) . " VND");
        } else {
            $this->info("Không có NPP nào đủ điều kiện nhận thưởng trong tháng này.");
        }
    }

    protected function checkQualified(Distributor $distributor, int $year, int $month): bool
    {
        // Lấy dữ liệu tháng hiện tại
        $currentStat = $distributor->monthlyStats()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        // Kiểm tra nếu đã từng đủ điều kiện trước đó
        $wasQualified = $distributor->monthlyStats()
            ->where(function ($q) use ($year, $month) {
                $q->where('year', '<', $year)
                    ->orWhere(function ($q2) use ($year, $month) {
                        $q2->where('year', $year)->where('month', '<', $month);
                    });
            })
            ->where('is_qualified', true)
            ->exists();

        // Đếm số tháng KHÔNG đạt 5tr personal_sales
        $missedMonths = $distributor->monthlyStats()
            ->where('personal_sales', '<', 5000000)
            ->count();

        // Nếu đã từng đủ nhưng tháng hiện tại không đạt 5tr => không đủ
        if ($wasQualified) {
            if (!$currentStat || $currentStat->personal_sales < 5000000) return false;
            if ($missedMonths >= 5) return false; // mất danh hiệu
            return true; // giữ được danh hiệu
        }

        // Chưa từng đạt thì kiểm tra đủ 2 điều kiện:
        $dates = collect([
            Carbon::createFromDate($year, $month, 1),
            Carbon::createFromDate($year, $month, 1)->subMonth(),
            Carbon::createFromDate($year, $month, 1)->subMonths(2),
        ]);

        $stats = $distributor->monthlyStats()
            ->whereIn(DB::raw("CONCAT(year, '-', LPAD(month, 2, '0'))"),
                $dates->map(fn($d) => $d->format('Y-m'))->toArray()
            )->get()->keyBy(fn($s) => $s->year . '-' . str_pad($s->month, 2, '0', STR_PAD_LEFT));

        // Điều kiện 1: doanh số cá nhân >= 5tr trong 3 tháng liên tiếp
        $personalOk = $dates->every(function ($date) use ($stats) {
            $key = $date->format('Y-m');
            return isset($stats[$key]) && $stats[$key]->personal_sales >= 5000000;
        });

        if (!$personalOk) return false;

        // Điều kiện 2: có 2 nhánh đạt chuẩn trong 3 tháng liên tiếp
        $qualifiedBranches = 0;

        foreach ($distributor->children as $child) {
            $branchOk = $dates->every(function ($date) use ($child) {
                return $child->totalBranchSale($date->year, $date->month) >= 250000000;
            });

            if ($branchOk) {
                $qualifiedBranches++;
            }

            if ($qualifiedBranches >= 2) break;
        }

        return $qualifiedBranches >= 2;
    }
}
