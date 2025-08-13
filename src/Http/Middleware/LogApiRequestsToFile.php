<?php

namespace Ezmu\DocsGenerator\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LogApiRequestsToFile {

    public function handle(Request $request, Closure $next) {

        $response = $next($request);

        if ($request->is('api/*')) {
            $logData = [
                'timestamp' => now()->toDateTimeString(),
                'method' => $request->method(),
                'uri' => $request->path(),
                'request_headers' => $request->headers->all(),
                'request_body' => $request->all(),
                'response_status' => $response->status(),
                'response_body' => method_exists($response, 'getContent') ? $response->getContent() : (string) $response,
            ];

            $safeUri = str_replace('/', '_', $request->path());
            $filename = now()->format('Ymd_His') . "_{$safeUri}_" . uniqid() . ".json";

            // Use storage_path('app/docs') if you want logs with your docs
            $docsPath = storage_path('app/docs/api-log');
            if (!\Illuminate\Support\Facades\File::exists($docsPath)) {
                \Illuminate\Support\Facades\File::makeDirectory($docsPath, 0755, true);
            }

            $fullPath = $docsPath . DIRECTORY_SEPARATOR . $filename;

            \Illuminate\Support\Facades\File::put($fullPath, json_encode($logData, JSON_PRETTY_PRINT));
        }

        return $response;
    }

}
