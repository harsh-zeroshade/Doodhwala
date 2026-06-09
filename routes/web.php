<?php

use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MilkmanDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get("/", [HomeController::class, "index"])->name("home");

Route::get("/locale/{locale}", [LocaleController::class, "switch"])->name(
    "locale.switch",
);

Route::middleware(["auth"])->group(function () {
    Route::get("/dashboard", function () {
        $user = auth()->user();

        return redirect()->to(
            $user->isAdmin()
                ? route("admin.route")
                : route("customer.overview"),
        );
    })->name("dashboard");

    Route::middleware("role:admin")
        ->prefix("admin")
        ->name("admin.")
        ->group(function () {
            Route::get("/route", [
                MilkmanDashboardController::class,
                "route",
            ])->name("route");
            Route::get("/status", [
                MilkmanDashboardController::class,
                "status",
            ])->name("status");
            Route::get("/ledger", [
                MilkmanDashboardController::class,
                "ledger",
            ])->name("ledger");

            Route::post("/houses/{house}/delivery", [
                MilkmanDashboardController::class,
                "updateDelivery",
            ])->name("delivery.update");
            Route::post("/houses/{house}/update", [
                MilkmanDashboardController::class,
                "updateHouse",
            ])->name("house.update");
            Route::post("/status/broadcast", [
                MilkmanDashboardController::class,
                "broadcastStatus",
            ])->name("status.broadcast");
            Route::post("/houses/{house}/payment", [
                MilkmanDashboardController::class,
                "recordPayment",
            ])->name("payment.record");
            Route::post("/extra-orders/{extraOrder}/approve", [
                MilkmanDashboardController::class,
                "approveExtraOrder",
            ])->name("extra-orders.approve");
        });

    Route::middleware("role:customer")
        ->prefix("customer")
        ->name("customer.")
        ->group(function () {
            Route::get("/overview", [
                CustomerDashboardController::class,
                "overview",
            ])->name("overview");
            Route::get("/calendar", [
                CustomerDashboardController::class,
                "calendar",
            ])->name("calendar");
            Route::get("/requests", [
                CustomerDashboardController::class,
                "requests",
            ])->name("requests");

            Route::post("/requests", [
                CustomerDashboardController::class,
                "storeRequest",
            ])->name("requests.store");
            Route::delete("/requests/{extraOrder}", [
                CustomerDashboardController::class,
                "cancelRequest",
            ])->name("requests.cancel");
            Route::get("/calendar/day", [
                CustomerDashboardController::class,
                "dayDetail",
            ])->name("calendar.day");
        });

    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit",
    );
    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update",
    );
    Route::post("/profile/avatar", [
        ProfileController::class,
        "updateAvatar",
    ])->name("profile.avatar");
    Route::delete("/profile/avatar", [
        ProfileController::class,
        "removeAvatar",
    ])->name("profile.avatar.remove");
    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy",
    );
});

require __DIR__ . "/auth.php";
