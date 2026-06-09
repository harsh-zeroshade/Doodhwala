<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\ExtraOrder;
use App\Models\House;
use App\Models\LiveStatus;
use App\Models\Payment;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MilkmanDashboardController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

    public function route(Request $request): View
    {
        $date = Carbon::parse($request->query("date", today()->toDateString()));
        $houses = $this->loadRouteHouses($date);

        $delivered = $houses->where("route_state", "delivered")->count();
        $skipped = $houses->where("route_state", "skipped")->count();
        $pending = $houses->where("route_state", "pending")->count();
        $total = $houses->count();
        $progress =
            $total > 0 ? round((($delivered + $skipped) / $total) * 100) : 0;

        return view("admin.route", [
            "date" => $date,
            "houses" => $houses,
            "stats" => compact("delivered", "skipped", "pending", "progress"),
            "pageTitle" => __("Daily Route"),
            "activeNav" => "route",
        ]);
    }

    public function status(): View
    {
        $lastBroadcast = LiveStatus::query()->latest("created_at")->first();
        $customerCount = House::count();

        return view("admin.status", [
            "lastBroadcast" => $lastBroadcast,
            "customerCount" => $customerCount,
            "pageTitle" => __("Broadcast"),
            "activeNav" => "status",
            "defaultMessage" => __("On the Way 🛵"),
        ]);
    }

    public function ledger(Request $request): View
    {
        $month = Carbon::parse(
            $request->query("month", now()->format("Y-m")),
        )->startOfMonth();
        $houses = House::query()->orderBy("route_order")->with("user")->get();
        $ledger = $this->ledger->aggregateLedger($houses, $month);

        return view("admin.ledger", [
            "month" => $month,
            "ledger" => $ledger,
            "pageTitle" => __("Ledger"),
            "activeNav" => "ledger",
        ]);
    }

    public function updateDelivery(Request $request, House $house): JsonResponse
    {
        $data = $request->validate([
            "delivery_date" => ["required", "date"],
            "status" => ["required", "in:delivered,skipped,pending"],
            "cow_milk" => ["nullable", "numeric", "min:0"],
            "buffalo_milk" => ["nullable", "numeric", "min:0"],
        ]);

        $date = Carbon::parse($data["delivery_date"])->toDateString();

        if ($data["status"] === "pending") {
            DailyAttendance::query()
                ->where("house_id", $house->id)
                ->whereDate("delivery_date", $date)
                ->delete();

            return response()->json(["success" => true, "status" => "pending"]);
        }

        $cow = $data["cow_milk"] ?? $house->default_cow_milk;
        $buffalo = $data["buffalo_milk"] ?? $house->default_buffalo_milk;

        $attendance = DailyAttendance::query()->updateOrCreate(
            ["house_id" => $house->id, "delivery_date" => $date],
            [
                "status" => $data["status"],
                "cow_milk_delivered" =>
                    $data["status"] === "delivered" ? $cow : 0,
                "buffalo_milk_delivered" =>
                    $data["status"] === "delivered" ? $buffalo : 0,
                "cow_price_per_liter" => config(
                    "doodhwala.cow_price_per_liter",
                ),
                "buffalo_price_per_liter" => config(
                    "doodhwala.buffalo_price_per_liter",
                ),
            ],
        );

        return response()->json([
            "success" => true,
            "status" => $attendance->status,
            "cow_milk" => (float) $attendance->cow_milk_delivered,
            "buffalo_milk" => (float) $attendance->buffalo_milk_delivered,
        ]);
    }

    public function broadcastStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            "message" => ["required", "string", "max:500"],
        ]);

        $status = LiveStatus::create([
            "message" => $data["message"],
            "created_at" => now(),
        ]);

        return response()->json([
            "success" => true,
            "message" => $status->message,
            "created_at" => $status->created_at->toIso8601String(),
        ]);
    }

    public function recordPayment(Request $request, House $house): JsonResponse
    {
        $data = $request->validate([
            "amount" => ["required", "numeric", "min:0.01"],
            "payment_mode" => ["required", "in:cash,upi"],
            "paid_at" => ["nullable", "date"],
        ]);

        Payment::create([
            "house_id" => $house->id,
            "amount" => $data["amount"],
            "payment_mode" => $data["payment_mode"],
            "paid_at" => $data["paid_at"] ?? today(),
        ]);

        $due = $this->ledger->monthDue(
            $house,
            Carbon::parse($data["paid_at"] ?? today())->startOfMonth(),
        );

        return response()->json([
            "success" => true,
            "due" => $due,
        ]);
    }

    public function approveExtraOrder(ExtraOrder $extraOrder): JsonResponse
    {
        $extraOrder->update(["status" => "approved"]);

        $house = House::find($extraOrder->house_id);
        $isToday = $extraOrder->order_date->isToday();

        if ($extraOrder->type === "hold" && $isToday) {
            // Mark today's delivery as skipped
            DailyAttendance::query()->updateOrCreate(
                [
                    "house_id" => $extraOrder->house_id,
                    "delivery_date" => $extraOrder->order_date,
                ],
                [
                    "status" => "skipped",
                    "cow_milk_delivered" => 0,
                    "buffalo_milk_delivered" => 0,
                    "cow_price_per_liter" => config(
                        "doodhwala.cow_price_per_liter",
                    ),
                    "buffalo_price_per_liter" => config(
                        "doodhwala.buffalo_price_per_liter",
                    ),
                ],
            );
        } elseif ($extraOrder->type === "extra" && $isToday) {
            // If the milkman already recorded a delivery for today, add the extra milk to it
            $attendance = DailyAttendance::query()
                ->where("house_id", $extraOrder->house_id)
                ->whereDate("delivery_date", $extraOrder->order_date)
                ->where("status", "delivered")
                ->first();

            if ($attendance) {
                $attendance->update([
                    "cow_milk_delivered" =>
                        $attendance->cow_milk_delivered +
                        $extraOrder->extra_cow_milk,
                    "buffalo_milk_delivered" =>
                        $attendance->buffalo_milk_delivered +
                        $extraOrder->extra_buffalo_milk,
                ]);
            }
        }

        // Compute updated totals so the JS can update the card immediately
        $updatedCow = 0;
        $updatedBuffalo = 0;
        if ($house && $isToday) {
            $approvedExtra = ExtraOrder::query()
                ->where("house_id", $house->id)
                ->where("type", "extra")
                ->where("status", "approved")
                ->whereDate("order_date", today())
                ->first();

            $attendance = DailyAttendance::query()
                ->where("house_id", $house->id)
                ->whereDate("delivery_date", today())
                ->first();

            $updatedCow =
                (float) ($attendance?->cow_milk_delivered ??
                    $house->default_cow_milk +
                        ($approvedExtra?->extra_cow_milk ?? 0));
            $updatedBuffalo =
                (float) ($attendance?->buffalo_milk_delivered ??
                    $house->default_buffalo_milk +
                        ($approvedExtra?->extra_buffalo_milk ?? 0));
        }

        return response()->json([
            "success" => true,
            "type" => $extraOrder->type,
            "house_id" => $extraOrder->house_id,
            "is_today" => $isToday,
            "order_date" => $extraOrder->order_date->toDateString(),
            "updated_cow" => $updatedCow,
            "updated_buffalo" => $updatedBuffalo,
        ]);
    }

    public function updateHouse(Request $request, House $house): JsonResponse
    {
        $data = $request->validate([
            "address" => ["sometimes", "string"],
            "default_cow_milk" => ["required", "numeric", "min:0"],
            "default_buffalo_milk" => ["required", "numeric", "min:0"],
        ]);

        $house->update($data);

        return response()->json([
            "success" => true,
            "address" => $house->address,
            "default_cow_milk" => (float) $house->default_cow_milk,
            "default_buffalo_milk" => (float) $house->default_buffalo_milk,
        ]);
    }

    private function loadRouteHouses(Carbon $date)
    {
        return House::query()
            ->orderBy("route_order")
            ->with("user")
            ->get()
            ->map(function (House $house) use ($date) {
                $attendance = DailyAttendance::query()
                    ->where("house_id", $house->id)
                    ->whereDate("delivery_date", $date)
                    ->first();

                $hold = ExtraOrder::query()
                    ->where("house_id", $house->id)
                    ->where("type", "hold")
                    ->where("status", "approved")
                    ->whereDate("order_date", $date)
                    ->exists();

                $extra = ExtraOrder::query()
                    ->where("house_id", $house->id)
                    ->where("type", "extra")
                    ->where("status", "approved")
                    ->whereDate("order_date", $date)
                    ->first();

                $cow =
                    $attendance?->cow_milk_delivered ??
                    $house->default_cow_milk + ($extra?->extra_cow_milk ?? 0);
                $buffalo =
                    $attendance?->buffalo_milk_delivered ??
                    $house->default_buffalo_milk +
                        ($extra?->extra_buffalo_milk ?? 0);

                if ($hold && !$attendance) {
                    $routeState = "skipped";
                    $holdBadge = true;
                } elseif ($attendance?->status === "delivered") {
                    $routeState = "delivered";
                    $holdBadge = false;
                } elseif ($attendance?->status === "skipped") {
                    $routeState = "skipped";
                    $holdBadge = $hold;
                } else {
                    $routeState = "pending";
                    $holdBadge = false;
                }

                $house->setAttribute("route_state", $routeState);
                $house->setAttribute("hold_badge", $holdBadge);
                $house->setAttribute("cow_milk", (float) $cow);
                $house->setAttribute("buffalo_milk", (float) $buffalo);
                $house->setAttribute("attendance_id", $attendance?->id);

                // All upcoming pending requests (today or future) so the
                // milkman can see and approve them regardless of which date
                // they were submitted for.
                $pendingRequests = ExtraOrder::query()
                    ->where("house_id", $house->id)
                    ->where("status", "pending")
                    ->where("order_date", ">=", today()->toDateString())
                    ->orderBy("order_date")
                    ->get();

                $house->setAttribute("pending_requests", $pendingRequests);

                return $house;
            });
    }
}
