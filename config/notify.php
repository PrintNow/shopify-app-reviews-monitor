<?php

return [
    'dingtalk' => [
        'enabled' => (bool)env('NOTIFY_DING_ENABLED', false),
        'token' => env('NOTIFY_DING_TALK_TOKEN'),
        'secret' => env('NOTIFY_DING_TALK_SECRET'),
    ],
];
