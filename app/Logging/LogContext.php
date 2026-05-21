<?php

namespace App\Logging;

use Illuminate\Support\Facades\Auth;

final class LogContext
{
    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        $user = Auth::user();

        return [
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'ip' => request()?->ip(),
            'request_id' => app()->bound('request.id') ? app('request.id') : null,
        ];
    }
}
