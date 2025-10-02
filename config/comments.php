<?php

return [
    'route_prefix' => 'api/comments',
    'middleware' => ['api', 'throttle:60,1'], // rate limit
    'max_depth' => 5,
    'auto_approve_authenticated' => true,
    'reply_only_to_approved_parent' => true,

    'guests' => [
        'allowed' => true,
        'require_email' => true,
        'cookie_name' => 'guest_fingerprint',
    ],
];
