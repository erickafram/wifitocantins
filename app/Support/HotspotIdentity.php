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
        $candidates = [
            $request->query('ip'),
            $request->query('ip_address'),
            $request->query('client_ip'),
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
     * Determine if the provided MAC is a locally administered (randomized) address.
     * 
     * IMPORTANT: Modern iOS (14+) and Android (10+) devices use randomized MACs
     * by default. These MACs have the "locally administered" bit set (bit 1 of
     * first byte), resulting in prefixes like 02:, 06:, 0A:, 0E:, etc.
     * 
     * These MACs are CONSISTENT per-network (same random MAC for same SSID),
     * so they are VALID identifiers for our hotspot authentication.
     * 
     * We should NOT treat them as "mock" or invalid - they represent real devices.
     * Only truly empty/null MACs should be considered invalid.
     */
    public static function isMockMac(?string $mac): bool
    {
        if (! $mac) {
            return true;
        }

        $normalized = strtoupper(trim($mac));

        // Only reject truly invalid/placeholder MACs
        return in_array($normalized, [
            '00:00:00:00:00:00',
            'FF:FF:FF:FF:FF:FF',
            'UNKNOWN',
            '',
        ]);
    }

    /**
     * Check if a MAC is locally administered (randomized).
     * This is informational only - randomized MACs are perfectly valid.
     */
    public static function isRandomizedMac(?string $mac): bool
    {
        if (! $mac || strlen($mac) < 2) {
            return false;
        }

        $firstByte = hexdec(substr(strtoupper(trim($mac)), 0, 2));

        return ($firstByte & 0x02) !== 0;
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
            // Try to get MAC from URL query params
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
            // Only nullify truly invalid MACs (00:00:00:00:00:00, etc.)
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
