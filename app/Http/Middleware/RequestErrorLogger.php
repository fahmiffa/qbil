<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestErrorLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya log jika status code >= 400 (Error)
        if ($response->getStatusCode() >= 400) {
            $this->logRequestError($request, $response);
        }

        return $response;
    }

    /**
     * Log the error details.
     */
    protected function logRequestError(Request $request, Response $response): void
    {
        $data = [
            'url'     => $request->fullUrl(),
            'method'  => $request->method(),
            'ip'      => $request->ip(),
            'status'  => $response->getStatusCode(),
            'user_id' => $request->user()?->id ?? 'Guest',
            'input'   => $this->maskSensitiveData($request->all()),
        ];

        // Jika respons adalah JSON, ambil isinya. Jika HTML, mungkin cukup statusnya saja atau cuplikan.
        if (str_contains($response->headers->get('Content-Type') ?? '', 'application/json')) {
            $data['response'] = json_decode($response->getContent(), true);
        }

        Log::error('Request Error Logged:', $data);
    }

    /**
     * Mask sensitive data like passwords.
     */
    protected function maskSensitiveData(array $data): array
    {
        $fieldsToMask = ['password', 'password_confirmation', 'token', 'secret', 'credit_card'];

        foreach ($data as $key => $value) {
            if (in_array($key, $fieldsToMask)) {
                $data[$key] = '********';
            } elseif (is_array($value)) {
                $data[$key] = $this->maskSensitiveData($value);
            }
        }

        return $data;
    }
}
