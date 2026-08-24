# Visor destino + marcado sin “número equivocado”

La documentación original de [custom_agent](https://github.com/Johnrivera7/filament-issabel-click-to-call/blob/main/docs/ISSABEL_VISOR_DESTINO.md) pone el celular en `CALLERID(num)` para el visor. En Issabel/FreePBX eso **rompe** la salida: al contestar el CID móvil entra a `from-internal` y suena *ha marcado el número equivocado* (`bad-number`).

## Solución (2 contextos)

| Contexto | Momento | Qué hace |
| --- | --- | --- |
| `filament-click-to-call` | Suena el anexo | Visor = celular (`CALLERID` name/num = destino) |
| `filament-ctc-out` | Al contestar | Pone `CALLERID` = anexo y `Goto(from-internal,destino,1)` |

## Pegar en Issabel

Archivo: `/etc/asterisk/extensions_custom.conf` — **ambos** bloques:

```ini
[filament-click-to-call]
exten => _X.,1,NoOp(Filament CTC ring anexo=${EXTEN} dest=${CTC_DEST})
 same => n,Set(CALLERID(name)=${IF($["${CTC_NAME}"=""]?${CTC_DEST}:${CTC_NAME})})
 same => n,Set(CALLERID(num)=${IF($["${CTC_NUM}"=""]?${CTC_DEST}:${CTC_NUM})})
 same => n,Set(CALLERID(name-pres)=allowed_not_screened)
 same => n,Set(CALLERID(num-pres)=allowed_not_screened)
 same => n,Dial(SIP/${EXTEN},30,m)
 same => n,Hangup()

[filament-ctc-out]
exten => s,1,NoOp(Filament CTC out dest=${CTC_DEST} anexo=${CTC_ANEXO})
 same => n,GotoIf($["${CTC_DEST}"=""]?fail)
 same => n,Set(CALLERID(num)=${IF($["${CTC_ANEXO}"=""]?${AMPUSER}:${CTC_ANEXO})})
 same => n,Set(CALLERID(name)=${CALLERID(num)})
 same => n,Goto(from-internal,${CTC_DEST},1)
 same => n(fail),Playback(ss-noservice)
 same => n,Hangup()
```

```bash
asterisk -rx "dialplan reload"
```

Verificar:

```bash
asterisk -rx "dialplan show filament-ctc-out"
asterisk -rx "dialplan show filament-click-to-call"
```

## `.env` Laravel

```env
ISSABEL_PBX_ORIGINATE_STRATEGY=custom_agent
ISSABEL_PBX_AGENT_DIAL_CONTEXT=filament-click-to-call
ISSABEL_PBX_OUTBOUND_RESET_CONTEXT=filament-ctc-out
ISSABEL_PBX_CHANNEL_DRIVER=SIP
ISSABEL_PBX_DIAL_CONTEXT=from-internal
ISSABEL_PBX_DIAL_FORMAT=local_9
ISSABEL_PBX_CALLER_ID_DISPLAY=destination
```

## Flujo AMI

```text
Channel: Local/1015@filament-click-to-call
Context: filament-ctc-out
Exten: s
Variable: __CTC_DEST=955170937
Variable: __CTC_NAME=9 5517 0937
Variable: __CTC_NUM=955170937
Variable: __CTC_ANEXO=1015
```

1. Suena `1015` con visor = `955170937`.
2. Al contestar → `filament-ctc-out` pone CID=`1015` → marca `955170937` por `from-internal`.
