<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Issabel / Asterisk AMI (click-to-call)
    |--------------------------------------------------------------------------
    | Create the AMI user on Issabel: Advanced Settings → Asterisk Manager Users
    | or edit /etc/asterisk/manager.conf — permit only originate/read, bind to
    | the Laravel server IP. Default AMI port: 5038 (TCP, same LAN/VPC).
    */
    'enabled' => env('ISSABEL_CLICK_TO_CALL_ENABLED', true),

    'host' => env('ISSABEL_PBX_HOST', '127.0.0.1'),

    'port' => (int) env('ISSABEL_PBX_AMI_PORT', 5038),

    'username' => env('ISSABEL_PBX_AMI_USER'),

    'secret' => env('ISSABEL_PBX_AMI_SECRET'),

    'connect_timeout_seconds' => (int) env('ISSABEL_PBX_AMI_CONNECT_TIMEOUT', 5),

    'read_timeout_seconds' => (int) env('ISSABEL_PBX_AMI_READ_TIMEOUT', 10),

    /*
    | Channel driver for extensions: PJSIP (Issabel 4+) or SIP (legacy).
    | Originate channel becomes "{driver}/{extension}" e.g. PJSIP/2151
    */
    'channel_driver' => env('ISSABEL_PBX_CHANNEL_DRIVER', 'PJSIP'),

    /*
    | Dialplan context where outbound numbers are routed (Issabel default).
    */
    'dial_context' => env('ISSABEL_PBX_DIAL_CONTEXT', 'from-internal'),

    /*
    | Optional prefix prepended to normalized destination (trunk dial prefix).
    */
    'dial_prefix' => env('ISSABEL_PBX_DIAL_PREFIX', ''),

    /*
    | Outbound number format for Issabel dialplan:
    | - local_9: 955170937 (typical Chile mobile from from-internal)
    | - e164_cl: 56955170937 (if outbound routes require country code)
    */
    'dial_format' => env('ISSABEL_PBX_DIAL_FORMAT', 'local_9'),

    /*
    | AMI originate strategy:
    | - context_exten: ring agent, then run {dial_context}/{number} — works on most Issabel
    | - application_dial: ring agent, then Dial(Local/{number}@context)
    */
    'originate_strategy' => env('ISSABEL_PBX_ORIGINATE_STRATEGY', 'context_exten'),

    'caller_id_name' => env('ISSABEL_PBX_CALLER_ID_NAME', 'Filament Click-to-Call'),

    /*
    | What the agent phone display shows when click-to-call rings the extension:
    | - destination: customer phone (recommended)
    | - extension: agent anexo
    | - custom: ISSABEL_PBX_CALLER_ID_NUMBER
    */
    'caller_id_display' => env('ISSABEL_PBX_CALLER_ID_DISPLAY', 'destination'),

    'caller_id_number' => env('ISSABEL_PBX_CALLER_ID_NUMBER'),

    /*
    | Default extension when action does not resolve one (leave null).
    */
    'default_extension' => env('ISSABEL_PBX_DEFAULT_EXTENSION'),

    'navigation' => [
        'group' => 'Telefonía',
        'sort' => 50,
        'icon' => 'heroicon-o-phone',
        'label' => 'Issabel Click-to-Call',
    ],

    'register_settings_page' => true,
];
