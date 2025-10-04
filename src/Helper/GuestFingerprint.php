<?php

namespace Fiachehr\Comments\Helper;

use Illuminate\Support\Facades\Cookie;

class GuestFingerprint
{
    public static function generate(): string
    {
        $request = request();

        $data = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent() ?? '',
            'accept_language' => $request->header('Accept-Language'),
            'accept_encoding' => $request->header('Accept-Encoding') ?? '',
            'timezone' => $request->header('X-Timezone', 'UTC') ?? '',
            'screen_resolution' => $request->header('X-Screen-Resolution') ?? '',
            'color_depth' => $request->header('X-Color-Depth') ?? '',
        ];

        return hash('sha256', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public static function getOrCreate(): string
    {
        $cookieName = config('comments.guests.cookie_name', 'guest_fingerprint');

        if ($fingerprint = request()->cookie($cookieName)) {
            return $fingerprint;
        }

        $fingerprint = self::generate();
        Cookie::queue($cookieName, $fingerprint, 60 * 24 * 365);

        return $fingerprint;
    }

    public static function validate(string $fingerprint): bool
    {
        return strlen($fingerprint) === 64 && ctype_xdigit($fingerprint);
    }
}
