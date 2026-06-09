<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view("auth.register");
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            "name" => ["required", "string", "max:255"],
            // At least one identifier is required.
            "phone_number" => [
                "nullable",
                "required_without:email",
                "string",
                "max:20",
                "unique:" . User::class,
            ],
            "email" => [
                "nullable",
                "required_without:phone_number",
                "string",
                "email",
                "max:255",
                "unique:" . User::class,
            ],
            "password" => ["required", "confirmed", Rules\Password::defaults()],
        ]);

        // Always store a phone number on the user (generate a placeholder if only email is given).
        $phone =
            $request->input("phone_number") ?:
            "email-" .
                preg_replace(
                    "/[^a-z0-9]/i",
                    "",
                    strtolower($request->input("email")),
                ) .
                "-" .
                uniqid();

        $user = User::create([
            "name" => $request->name,
            "phone_number" => $phone,
            "email" => $request->email,
            "role" => User::ROLE_CUSTOMER,
            "password" => Hash::make($request->password),
        ]);

        // Create a default House record so the customer dashboard is accessible
        // immediately. The admin can update the address and milk quantities later.
        House::create([
            "user_id" => $user->id,
            "customer_name" => $user->name,
            "address" => "",
            "route_order" => 0,
            "default_cow_milk" => 0,
            "default_buffalo_milk" => 0,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(
            route("customer.overview", absolute: false),
        );
    }
}
