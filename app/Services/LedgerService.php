<?php

namespace App\Services;

use App\Models\DailyAttendance;
use App\Models\House;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LedgerService
{
    public function monthBill(House $house, Carbon $month): float
    {
        return (float) DailyAttendance::query()
            ->where('house_id', $house->id)
            ->where('status', 'delivered')
            ->whereYear('delivery_date', $month->year)
            ->whereMonth('delivery_date', $month->month)
            ->get()
            ->sum(fn (DailyAttendance $a) => $a->totalAmount());
    }

    public function monthCollected(House $house, Carbon $month): float
    {
        return (float) Payment::query()
            ->where('house_id', $house->id)
            ->whereYear('paid_at', $month->year)
            ->whereMonth('paid_at', $month->month)
            ->sum('amount');
    }

    public function monthDue(House $house, Carbon $month): float
    {
        return max(0, $this->monthBill($house, $month) - $this->monthCollected($house, $month));
    }

    public function monthLiters(House $house, Carbon $month): float
    {
        return (float) DailyAttendance::query()
            ->where('house_id', $house->id)
            ->where('status', 'delivered')
            ->whereYear('delivery_date', $month->year)
            ->whereMonth('delivery_date', $month->month)
            ->get()
            ->sum(fn (DailyAttendance $a) => $a->totalLiters());
    }

    public function deliveryCount(House $house, Carbon $month): int
    {
        return DailyAttendance::query()
            ->where('house_id', $house->id)
            ->where('status', 'delivered')
            ->whereYear('delivery_date', $month->year)
            ->whereMonth('delivery_date', $month->month)
            ->count();
    }

    public function aggregateLedger(Collection $houses, Carbon $month): array
    {
        $totalCollected = 0;
        $totalOutstanding = 0;
        $rows = [];

        foreach ($houses as $house) {
            $due = $this->monthDue($house, $month);
            $collected = $this->monthCollected($house, $month);
            $totalCollected += $collected;
            $totalOutstanding += $due;

            $rows[] = [
                'house' => $house,
                'deliveries' => $this->deliveryCount($house, $month),
                'liters' => $this->monthLiters($house, $month),
                'due' => $due,
                'collected' => $collected,
            ];
        }

        return [
            'rows' => $rows,
            'total_collected' => $totalCollected,
            'total_outstanding' => $totalOutstanding,
        ];
    }
}
