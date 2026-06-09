<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\ExtraOrder;
use App\Models\LiveStatus;
use App\Models\User;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

    public function overview(): View
    {
        $house = auth()->user()->house;
        abort_unless($house, 404);

        $month = now()->startOfMonth();
        $weekStart = now()->startOfWeek();

        $weekAttendance = DailyAttendance::query()
            ->where("house_id", $house->id)
            ->where("status", "delivered")
            ->whereBetween("delivery_date", [
                $weekStart->toDateString(),
                now()->toDateString(),
            ])
            ->get();

        $weekCow = $weekAttendance->sum("cow_milk_delivered");
        $weekBuffalo = $weekAttendance->sum("buffalo_milk_delivered");
        $weekDue = $weekAttendance->sum(fn($a) => $a->totalAmount());

        $liveStatus = LiveStatus::query()->latest("created_at")->first();
        $admin = User::query()->where("role", User::ROLE_ADMIN)->first();

        return view("customer.overview", [
            "house" => $house,
            "pendingDue" => $this->ledger->monthDue($house, $month),
            "consumedLiters" => $this->ledger->monthLiters($house, $month),
            "monthLabel" => $month->translatedFormat("F Y"),
            "weekCow" => $weekCow,
            "weekBuffalo" => $weekBuffalo,
            "weekDue" => $weekDue,
            "liveStatus" => $liveStatus,
            "milkmanName" => $admin?->name ?? __("Milkman"),
            "pageTitle" => __("Overview"),
            "activeNav" => "overview",
        ]);
    }

    public function calendar(Request $request): View
    {
        $house = auth()->user()->house;
        abort_unless($house, 404);

        $month = Carbon::parse(
            $request->query("month", now()->format("Y-m")),
        )->startOfMonth();
        $daysInMonth = $month->daysInMonth;
        $firstWeekday = $month->dayOfWeek;

        $records = DailyAttendance::query()
            ->where("house_id", $house->id)
            ->whereYear("delivery_date", $month->year)
            ->whereMonth("delivery_date", $month->month)
            ->get()
            ->keyBy(fn($a) => $a->delivery_date->day);

        $deliveredCount = $records->where("status", "delivered")->count();

        return view("customer.calendar", [
            "house" => $house,
            "month" => $month,
            "daysInMonth" => $daysInMonth,
            "firstWeekday" => $firstWeekday,
            "records" => $records,
            "deliveredCount" => $deliveredCount,
            "monthSpend" => $this->ledger->monthBill($house, $month),
            "monthPaid" => $this->ledger->monthCollected($house, $month),
            "monthDue" => $this->ledger->monthDue($house, $month),
            "monthLiters" => $this->ledger->monthLiters($house, $month),
            "pageTitle" => __("Delivery Calendar"),
            "activeNav" => "calendar",
        ]);
    }

    public function requests(): View
    {
        $house = auth()->user()->house;
        abort_unless($house, 404);

        $upcoming = ExtraOrder::query()
            ->where("house_id", $house->id)
            ->where("order_date", ">=", today())
            ->whereIn("status", ["pending", "approved"])
            ->orderBy("order_date")
            ->get();

        return view("customer.requests", [
            "house" => $house,
            "upcoming" => $upcoming,
            "pageTitle" => __("Advance Requests"),
            "activeNav" => "requests",
        ]);
    }

    public function storeRequest(Request $request): JsonResponse
    {
        $house = auth()->user()->house;
        abort_unless($house, 404);

        $data = $request->validate([
            "order_date" => ["required", "date", "after_or_equal:today"],
            "type" => ["required", "in:hold,extra"],
            "extra_cow_milk" => ["nullable", "numeric", "min:0"],
            "extra_buffalo_milk" => ["nullable", "numeric", "min:0"],
            "note" => ["nullable", "string", "max:500"],
        ]);

        $order = ExtraOrder::create([
            "house_id" => $house->id,
            "order_date" => $data["order_date"],
            "type" => $data["type"],
            "extra_cow_milk" =>
                $data["type"] === "extra" ? $data["extra_cow_milk"] ?? 0 : 0,
            "extra_buffalo_milk" =>
                $data["type"] === "extra"
                    ? $data["extra_buffalo_milk"] ?? 0
                    : 0,
            "note" => $data["note"] ?? null,
            "status" => "pending",
        ]);

        return response()->json([
            "success" => true,
            "order" => [
                "id" => $order->id,
                "order_date" => $order->order_date->format("M j, Y"),
                "type" => $order->type,
                "extra_cow_milk" => (float) $order->extra_cow_milk,
                "extra_buffalo_milk" => (float) $order->extra_buffalo_milk,
                "note" => $order->note,
                "status" => $order->status,
            ],
        ]);
    }

    public function cancelRequest(ExtraOrder $extraOrder): JsonResponse
    {
        abort_unless($extraOrder->house_id === auth()->user()->house?->id, 403);
        abort_unless(
            in_array($extraOrder->status, ["pending", "approved"], true),
            422,
        );

        $extraOrder->delete();

        return response()->json(["success" => true]);
    }

    public function dayDetail(Request $request): JsonResponse
    {
        $house = auth()->user()->house;
        abort_unless($house, 404);

        $data = $request->validate([
            "date" => ["required", "date"],
        ]);

        $date = Carbon::parse($data["date"]);
        $attendance = DailyAttendance::query()
            ->where("house_id", $house->id)
            ->whereDate("delivery_date", $date)
            ->first();

        if (
            $date->isToday() &&
            (!$attendance || $attendance->status !== "delivered")
        ) {
            return response()->json([
                "type" => "today",
                "message" => __("Delivery in progress today"),
                "sub" => __("Milkman is on the way"),
            ]);
        }

        if ($date->isFuture()) {
            return response()->json([
                "type" => "future",
                "message" => __("No delivery data yet."),
            ]);
        }

        if (!$attendance || $attendance->status === "skipped") {
            return response()->json([
                "type" => "skipped",
                "message" => __("No Delivery"),
                "sub" => __("Milkman was on leave / holiday"),
            ]);
        }

        return response()->json([
            "type" => "delivered",
            "total" => $attendance->totalLiters(),
            "cow" => (float) $attendance->cow_milk_delivered,
            "buffalo" => (float) $attendance->buffalo_milk_delivered,
        ]);
    }
}
