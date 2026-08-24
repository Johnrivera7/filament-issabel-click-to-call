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
    | AMI originate strategy:
    | - application_dial: ring agent, then Dial(Local/{number}@context) — recommended for Issabel/FreePBX
    | - context_exten: ring agent, then run context/exten on answer (legacy)
    */
    'originate_strategy' => env('ISSABEL_PBX_ORIGINATE_STRATEGY', 'application_dial'),

    'caller_id_name' => env('ISSABEL_PBX_CALLER_ID_NAME', 'Filament Click-to-Call'),

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
