<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;

class CheckPolicyAcknowledgements
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // We do not intercept routes that are related to viewing/acknowledging the policy itself or logging out.
        if ($request->routeIs('policy.review') || $request->routeIs('policy.acknowledge') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Exclude admin and librarian roles from mandatory policy acknowledgement
        $roleName = strtolower($user->role->name ?? '');
        if (in_array($roleName, ['superadmin', 'admin', 'librarian', 'head_admin'])) {
            return $next($request);
        }

        $settings = SystemSetting::pluck('setting_value', 'setting_key')->toArray();

        // Check Terms
        $termsRequired = filter_var($settings['terms_require_acknowledgement'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $termsVersion = (int)($settings['terms_version'] ?? 0);
        if ($termsRequired && (int)$user->terms_acknowledged_version < $termsVersion) {
            return redirect()->route('policy.review');
        }

        // Check Privacy
        $privacyRequired = filter_var($settings['privacy_require_acknowledgement'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $privacyVersion = (int)($settings['privacy_version'] ?? 0);
        if ($privacyRequired && (int)$user->privacy_acknowledged_version < $privacyVersion) {
            return redirect()->route('policy.review');
        }

        // Check Cookie
        $cookieRequired = filter_var($settings['cookie_require_acknowledgement'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $cookieVersion = (int)($settings['cookie_version'] ?? 0);
        if ($cookieRequired && (int)$user->cookie_acknowledged_version < $cookieVersion) {
            return redirect()->route('policy.review');
        }

        return $next($request);
    }
}
