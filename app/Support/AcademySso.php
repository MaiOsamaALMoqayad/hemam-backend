<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cross-site login with the academy (hemam-academy-api).
 *
 * No shared secret: the caller proves it already has an academy session by
 * handing over its academy token, which we check by asking the academy API
 * who it belongs to. The accounts must be linked on BOTH sides
 * (users.academy_user_id here, users.site_user_id there), so a compromise of
 * one database can't mint sessions on the other.
 */
class AcademySso
{
    /** Lifetime of a token issued by an exchange; matches the FE cookies. */
    public const TOKEN_DAYS = 7;

    /**
     * The site admin a valid, mutually linked academy token maps to, or null.
     */
    public static function resolveUser(string $academyToken): ?User
    {
        $academyUser = self::fetchAcademyUser($academyToken);
        if ($academyUser === null) {
            return null;
        }

        $academyUserId = $academyUser['id'] ?? null;
        $siteUserId = $academyUser['site_user_id'] ?? null;
        if (! is_int($academyUserId) || ! is_int($siteUserId) || ($academyUser['is_active'] ?? false) !== true) {
            return null;
        }

        return User::query()
            ->whereKey($siteUserId)
            ->where('academy_user_id', $academyUserId)
            ->first();
    }

    /**
     * The academy's `GET /api/v1/auth/me` for the token, or null when the
     * token is rejected or the academy can't be reached.
     *
     * @return array<string, mixed>|null
     */
    private static function fetchAcademyUser(string $academyToken): ?array
    {
        $base = rtrim((string) config('services.academy_sso.api_url'), '/');
        if ($base === '') {
            return null;
        }

        try {
            $response = Http::acceptJson()
                ->withToken($academyToken)
                ->timeout(5)
                ->get("{$base}/api/v1/auth/me");
        } catch (\Throwable $e) {
            Log::warning('SSO: academy API unreachable: '.$e->getMessage());

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $user = $response->json('data');

        return is_array($user) ? $user : null;
    }
}
