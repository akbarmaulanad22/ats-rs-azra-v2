<?php

namespace App\Http\Middleware;

use App\Logging\LogContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogRequestMiddleware
{
    private const IGNORED_EXTENSIONS = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'map'];

    public function handle(Request $request, Closure $next): Response
    {
        $requestId = Str::uuid()->toString();
        app()->instance('request.id', $requestId);

        $startTime = microtime(true);

        $response = $next($request);

        if ($this->shouldLog($request)) {
            $duration = round((microtime(true) - $startTime) * 1000);

            Log::info('HTTP request handled', array_merge(LogContext::make(), [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status' => $response->getStatusCode(),
                'duration_ms' => $duration,
            ]));
        }

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        $path = $request->path();
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return ! in_array(strtolower($extension), self::IGNORED_EXTENSIONS, true);
    }
}
