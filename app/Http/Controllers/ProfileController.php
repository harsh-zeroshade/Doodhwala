<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view("profile.edit", [
            "user" => $user,
            "pageTitle" => __("Profile"),
            "backUrl" => route(
                $user->isAdmin() ? "admin.route" : "customer.overview",
            ),
            "activeNav" => "profile",
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        // If the email was changed, mark it as unverified so the user
        // (when email verification is required) has to re-confirm.
        if ($user->isDirty("email")) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route("profile.edit")->with(
            "status",
            "profile-updated",
        );
    }

    /**
     * Delete the user's account.
     */
    /** Resolve the correct storage disk (S3/R2 in production, public locally). */
    private function avatarDisk(): string
    {
        return env("AWS_BUCKET") ? "s3" : "public";
    }

    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk($this->avatarDisk())->delete($user->avatar);
            $user->update(["avatar" => null]);
        }

        return back()->with("status", "avatar-removed");
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            "avatar" => [
                "required",
                "image",
                "mimes:jpg,jpeg,png,webp",
                "max:2048",
            ],
        ]);

        $user = $request->user();
        $disk = $this->avatarDisk();

        if ($user->avatar) {
            Storage::disk($disk)->delete($user->avatar);
        }

        $path = $request->file("avatar")->store("avatars", $disk);
        $user->update(["avatar" => $path]);

        return back()->with("status", "avatar-updated");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag("userDeletion", [
            "password" => ["required", "current_password"],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to("/");
    }
}
