<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'message',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
