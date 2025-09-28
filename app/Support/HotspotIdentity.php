<?php

namespace App\Support;

use App\Models\MikrotikMacReport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HotspotIdentity
{
    /**
     * Resolve the best client IP considering proxy headers.
     */
    public static function resolveClientIp(Request $request): ?string
    {
        foreach (['ip', 'ip_address', 'client_ip'] as $queryKey) {
            $value = $request->query($queryKey);
            if ($value && filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            }
        }

        if ($referer = $request->headers->get('referer')) {
            $parsed = parse_url($referer);
            if (! empty($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                if (! empty($queryParams['ip']) && filter_var($queryParams['ip'], FILTER_VALIDATE_IP)) {
                    return $queryParams['ip'];
                }
            }
        }

        $candidates = [
            $request->input('ip'),
            $request->input('ip_address'),
            $request->input('client_ip'),
            $request->header('CF-Connecting-IP'),
            $request->header('X-Client-IP'),
            $request->header('X-Forwarded-For'),
            $request->header('X-Real-IP'),
        ];

        foreach ($candidates as $value) {
            if (! $value) {
                continue;
            }

            $ip = trim(explode(',', $value)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return $request->ip();
    }

    /**
     * Normalize MAC formatting (uppercase, colon separated).
     */
    public static function normalizeMac(?string $mac): ?string
    {
        if (! $mac) {
            return null;
        }

        $normalized = strtoupper(str_replace('-', ':', trim($mac)));

        return Str::length($normalized) === 17 ? $normalized : null;
    }

    /**
     * Determine if the provided MAC is a locally administered/mock address.
     */
    public static function isMockMac(?string $mac): bool
    {
        if (! $mac) {
            return true;
        }

        return Str::startsWith(strtolower($mac), '02:');
    }

    /**
     * Decide whether a new MAC should replace the current one.
     */
    public static function shouldReplaceMac(?string $current, ?string $candidate): bool
    {
        if (! $candidate) {
            return false;
        }

        if (! $current) {
            return true;
        }

        $currentMock = self::isMockMac($current);
        $candidateMock = self::isMockMac($candidate);

        if ($currentMock && ! $candidateMock) {
            return true;
        }

        if (! $currentMock && $candidateMock) {
            return false;
        }

        return strcasecmp($current, $candidate) !== 0;
    }

    /**
     * Try to obtain a real MAC for the given IP using Mikrotik reports.
     */
    public static function resolveRealMac(?string $mac, ?string $ip): ?string
    {
        $normalizedMac = self::normalizeMac($mac);

        if (! $normalizedMac) {
            $queryMac = self::normalizeMac(request()->query('mac'));
            if ($queryMac && ! self::isMockMac($queryMac)) {
                $normalizedMac = $queryMac;
            } elseif ($referer = request()->headers->get('referer')) {
                $parsed = parse_url($referer);
                if (! empty($parsed['query'])) {
                    parse_str($parsed['query'], $queryParams);
                    if (! empty($queryParams['mac'])) {
                        $refererMac = self::normalizeMac($queryParams['mac']);
                        if ($refererMac && ! self::isMockMac($refererMac)) {
                            $normalizedMac = $refererMac;
                        }
                    }
                }
            }
        } elseif (self::isMockMac($normalizedMac)) {
            $normalizedMac = null;
        }

        if (! $ip) {
            return $normalizedMac;
        }

        $report = MikrotikMacReport::getLatestMacForIp($ip);

        if ($report && self::shouldReplaceMac($normalizedMac, $report->mac_address)) {
            return $report->mac_address;
        }

        return $normalizedMac;
    }
}
