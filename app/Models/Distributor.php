<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class Distributor extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'parent_id',
    ];

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Distributor::class, 'parent_id');
    }

    public function monthlyStats(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DistributorMonthlyStats::class, 'distributor_id');
    }

    public function totalBranchSale(int $year = null, int $month = null)
    {
        $now = now();

        $year = $year ?? $now->year;
        $month = $month ?? $now->month;

        $personal = $this->monthlyStats()
            ->where('year', $year)
            ->where('month', $month)
            ->value('personal_sales') ?? 0;

        $children = $this->relationLoaded('children') ? $this->children : $this->children()->get();

        foreach ($children as $child) {
            $personal += $child->totalBranchSale($year, $month);
        }

        return $personal;
    }
}
