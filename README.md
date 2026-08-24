# Issabel Click-to-Call

<img src="https://raw.githubusercontent.com/Johnrivera7/filament-issabel-click-to-call/main/art/banner.jpg" alt="Issabel Click-to-Call Filament plugin" class="filament-hidden" />

<p align="center">
    <a href="https://filamentphp.com/docs/4.x/panels/installation">
        <img alt="FILAMENT 4.x" src="https://img.shields.io/badge/FILAMENT-4.x-EBB304?style=for-the-badge">
    </a>
    <a href="https://filamentphp.com/docs/5.x/panels/installation">
        <img alt="FILAMENT 5.x" src="https://img.shields.io/badge/FILAMENT-5.x-EBB304?style=for-the-badge">
    </a>
</p>

<p align="center">
    <a href="https://packagist.org/packages/johnrivera7/filament-issabel-click-to-call">
        <img alt="Latest Version on Packagist" src="https://img.shields.io/packagist/v/johnrivera7/filament-issabel-click-to-call.svg?style=flat-square&label=packagist">
    </a>
    <a href="https://github.com/Johnrivera7/filament-issabel-click-to-call/blob/main/LICENSE">
        <img alt="License" src="https://img.shields.io/packagist/l/johnrivera7/filament-issabel-click-to-call.svg?style=flat-square">
    </a>
</p>

Filament plugin for **[Issabel](https://www.issabel.com/)** PBX **click-to-call** via **Asterisk AMI**: ring the agent extension first, then dial the customer number from tables or forms.

> **Open source (MIT).** Works with Issabel and other Asterisk/FreePBX deployments that expose AMI on port **5038**.

## Requirements

| Stack | Versions |
| --- | --- |
| PHP | 8.2+ |
| Laravel | 11.28+ / 12+ / **13+** |
| Filament | **4.x** or **5.x** |
| Issabel | AMI enabled; agent softphone registered on extension |

## How click-to-call works

1. User clicks **Llamar / Call** in Filament.
2. Laravel connects to Issabel **AMI** (TCP 5038).
3. Sends `Originate`: channel `PJSIP/{anexo}` → when answered, dials destination in `from-internal`.
4. Agent answers on their softphone/IP phone; Issabel connects the customer.

Filament does **not** carry audio — the PBX does.

## Where to create AMI credentials (Issabel)

### Option A — Issabel web UI

1. Log in to Issabel as admin.
2. Open **Settings → Advanced Settings → Asterisk Manager Users** (wording may vary by Issabel version).
3. Add user e.g. `kitinicio_originate` with a strong secret.
4. Restrict **permit** to the **private IP of your Laravel server** only (not `0.0.0.0/0`).
5. Grant at least **write = originate** (and minimal read permissions).
6. Apply config / reload Asterisk.

### Option B — `/etc/asterisk/manager.conf`

```ini
[kitinicio_originate]
secret = YOUR_STRONG_SECRET
deny=0.0.0.0/0.0.0.0
permit=172.16.10.20/255.255.255.255
read = system,call,log,verbose,command,agent,user,config,dtmf,reporting,cdr,dialplan
write = originate
```

Then: `asterisk -rx "manager reload"` (or restart Asterisk from Issabel).

### `.env` in Laravel

```env
ISSABEL_CLICK_TO_CALL_ENABLED=true
ISSABEL_PBX_HOST=172.16.10.50
ISSABEL_PBX_AMI_PORT=5038
ISSABEL_PBX_AMI_USER=kitinicio_originate
ISSABEL_PBX_AMI_SECRET=YOUR_STRONG_SECRET
ISSABEL_PBX_CHANNEL_DRIVER=PJSIP
ISSABEL_PBX_DIAL_CONTEXT=from-internal
```

## Network / Huawei Cloud

You **do not** need to expose port **5038** to the public internet.

| Scenario | Recommendation |
| --- | --- |
| Laravel + Issabel **same VPC / LAN** | Security group: inbound **TCP 5038** only from Laravel VM private IP → Issabel private IP |
| Laravel outside, Issabel inside | VPN or private peering; avoid public AMI |
| Public AMI | **Not recommended** — AMI has full call control |

Issabel AMI listens on **5038/TCP** by default. Open it only between app server and PBX.

## Does Issabel have an API?

Yes, partially:

| API | Use for click-to-call? |
| --- | --- |
| **[Issabel REST `/pbxapi/`](https://github.com/IssabelFoundation/framework/tree/master/framework/html/pbxapi)** | Admin (extensions, ring groups). **No CDR/originate** in core API. |
| **Community [PBXAPI v2](https://github.com/wwwakcan/issabel-pbxapi-extension)** | Optional HTTP CDR + originate if installed on Issabel |
| **AMI (this plugin)** | **Recommended** for originate — native Asterisk, no extra modules |

This plugin uses **AMI** because it is stable, works on stock Issabel, and matches legacy cobranza integrations.

## Installation

```bash
composer require johnrivera7/filament-issabel-click-to-call
```

```bash
php artisan vendor:publish --tag=filament-issabel-click-to-call-config
```

Register in your `PanelProvider`:

```php
use JohnRivera7\FilamentIssabelClickToCall\FilamentIssabelClickToCallPlugin;

->plugin(
    FilamentIssabelClickToCallPlugin::make()
        ->navigationGroup('Telefonía')
)
```

## Filament action (table / form)

```php
use JohnRivera7\FilamentIssabelClickToCall\Actions\ClickToCallAction;

ClickToCallAction::make(
    phoneResolver: fn (array $record) => $record['celular'],
    extensionResolver: fn () => auth()->user()->cobranzaEjecutivo?->anexo_pbx,
)
```

Wire **extension per user** from your ejecutivo mantenedor (`anexo_pbx` column in your app).

## Multi-tenant credentials

```php
use JohnRivera7\FilamentIssabelClickToCall\Support\IssabelAmiCredentials;

FilamentIssabelClickToCallPlugin::make()
    ->credentialsUsing(fn (): IssabelAmiCredentials => IssabelAmiCredentials::fromArray(/* DB */))
    ->persistCredentialsUsing(function (IssabelAmiCredentials $c): void {
        /* save to DB */
    })
```

## Programmatic

```php
use JohnRivera7\FilamentIssabelClickToCall\FilamentIssabelClickToCallPlugin;

FilamentIssabelClickToCallPlugin::get()->clickToCall()->call(
    extension: '2151',
    phone: '957592274',
);
```

## Agent requirements

Each cobranza agent needs:

- Issabel **extension** (anexo) assigned in your mantenedor
- **Softphone** (Zoiper, MicroSIP, etc.) or IP phone **registered** to Issabel

Without a registered endpoint, Originate will fail or ring nowhere.

## License

MIT © John Rivera. Issabel® is a trademark of its respective owners; this plugin is not affiliated with Issabel LLC.

## Links

- [Issabel](https://www.issabel.com/)
- [Issabel REST API docs](https://github.com/IssabelFoundation/framework/tree/master/framework/html/pbxapi)
- [Filament plugins directory](https://filamentphp.com/plugins)
