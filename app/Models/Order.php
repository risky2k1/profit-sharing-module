<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'distributor_id',
        'total_amount',
        'ordered_at',
    ];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

}
