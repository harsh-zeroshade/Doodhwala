<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtraOrder extends Model
{
    protected $fillable = [
        'house_id',
        'order_date',
        'type',
        'extra_cow_milk',
        'extra_buffalo_milk',
        'note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'extra_cow_milk' => 'decimal:2',
            'extra_buffalo_milk' => 'decimal:2',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
}
