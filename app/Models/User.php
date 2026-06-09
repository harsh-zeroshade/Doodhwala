<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = "admin";

    public const ROLE_CUSTOMER = "customer";

    public function getDefaultRole(): string
    {
        return $this->role ?: self::ROLE_CUSTOMER;
    }

    protected $fillable = [
        "name",
        "phone_number",
        "email",
        "email_verified_at",
        "avatar",
        "role",
        "password",
    ];

    protected $hidden = ["password", "remember_token"];

    protected function casts(): array
    {
        return [
            "password" => "hashed",
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function house(): HasOne
    {
        return $this->hasOne(House::class);
    }

    public function avatarUrl(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        // Use S3/R2 in production (when AWS_BUCKET is set), public disk locally
        $disk = env("AWS_BUCKET") ? "s3" : "public";

        try {
            if (!Storage::disk($disk)->exists($this->avatar)) {
                return null;
            }
            return Storage::disk($disk)->url($this->avatar);
        } catch (\Throwable) {
            return null;
        }
    }

    public function initials(): string
    {
        $parts = preg_split("/\s+/", trim($this->name)) ?: [];

        if (count($parts) >= 2) {
            return strtoupper(
                mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1),
            );
        }

        return strtoupper(mb_substr($this->name, 0, 2));
    }
}
