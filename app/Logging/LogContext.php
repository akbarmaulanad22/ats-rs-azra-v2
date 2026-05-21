<?php

namespace App\Logging;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

final class LogContext
{
    /**
     * Build a standard context array for log entries.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public static function make(array $extra = []): array
    {
        $user = Auth::user();

        return array_merge([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'ip' => Request::ip(),
            'request_id' => app()->bound('request.id') ? app('request.id') : null,
        ], $extra);
    }
}
