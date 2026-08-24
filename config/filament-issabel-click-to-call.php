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
    | - local_9: 955170937
    | - outside_9: 9955170937 (prefix 9 for outside line — common in Chile PBX)
    | - e164_cl: 56955170937
    | - zero_nine: 0955170937
    */
    'dial_format' => env('ISSABEL_PBX_DIAL_FORMAT', 'local_9'),

    /*
    | Context for the agent leg (FreePBX/Issabel skips voicemail on no-answer).
    */
    'agent_context' => env('ISSABEL_PBX_AGENT_CONTEXT', 'from-internal'),

    /*
    | AMI originate strategy:
    | - application_dial: SIP/{anexo} + Dial(Local/{destino}) — probado en Issabel UAC
    | - local_agent: Local/{anexo}@agent_context → from-internal/{destino}
    | - context_exten: SIP/{anexo} → {dial_context}/{destino}
    */
    'originate_strategy' => env('ISSABEL_PBX_ORIGINATE_STRATEGY', 'application_dial'),

    'caller_id_name' => env('ISSABEL_PBX_CALLER_ID_NAME', 'Filament Click-to-Call'),

    /*
    | Agent phone display when click-to-call rings the extension:
    | - agent_to_destination: "2150 → 9 5517 0937" with anexo as number (recommended)
    | - destination: customer phone on both lines (legacy)
    | - extension: agent anexo only
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
