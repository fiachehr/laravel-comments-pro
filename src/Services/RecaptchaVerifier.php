<?php

namespace Fiachehr\Comments\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaVerifier
{
    public function __construct(
        protected ?string $secret,
        protected string $version = 'v3',
        protected float $score = 0.5
    ) {}

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (!$this->secret) return false;
        if (!$token) return false;

        $resp = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $this->secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if (!$resp->ok()) return false;
        $data = $resp->json();

        if (!($data['success'] ?? false)) return false;

        if ($this->version === 'v3') {
            return ($data['score'] ?? 0) >= $this->score;
        }

        return true; // v2 checkbox
    }
}
