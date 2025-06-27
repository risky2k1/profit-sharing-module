<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorMonthlyStats extends Model
{
    use HasFactory;

    protected $table = 'distributors_monthly_stats';

    protected $fillable = [
        'distributor_id',
        'year',
        'month',
        'personal_sales',
        'is_qualified',
    ];
}
