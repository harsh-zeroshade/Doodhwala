<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'address',
        'route_order',
        'default_cow_milk',
        'default_buffalo_milk',
    ];

    protected function casts(): array
    {
        return [
            'default_cow_milk' => 'decimal:2',
            'default_buffalo_milk' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailyAttendance(): HasMany
    {
        return $this->hasMany(DailyAttendance::class);
    }

    public function extraOrders(): HasMany
    {
        return $this->hasMany(ExtraOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->customer_name)) ?: [];

        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return strtoupper(mb_substr($this->customer_name, 0, 2));
    }
}
