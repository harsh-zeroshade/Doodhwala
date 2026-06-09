<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAttendance extends Model
{
    protected $table = 'daily_attendance';

    protected $fillable = [
        'house_id',
        'delivery_date',
        'status',
        'cow_milk_delivered',
        'buffalo_milk_delivered',
        'cow_price_per_liter',
        'buffalo_price_per_liter',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'cow_milk_delivered' => 'decimal:2',
            'buffalo_milk_delivered' => 'decimal:2',
            'cow_price_per_liter' => 'decimal:2',
            'buffalo_price_per_liter' => 'decimal:2',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function totalLiters(): float
    {
        return (float) $this->cow_milk_delivered + (float) $this->buffalo_milk_delivered;
    }

    public function totalAmount(): float
    {
        return ((float) $this->cow_milk_delivered * (float) $this->cow_price_per_liter)
            + ((float) $this->buffalo_milk_delivered * (float) $this->buffalo_price_per_liter);
    }
}
