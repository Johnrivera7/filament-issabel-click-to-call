# Visor del teléfono: mostrar el número destino (no el CNAM del anexo)

## Por qué hace falta un contexto custom en Issabel

En Issabel/FreePBX cada anexo tiene un **CNAM** configurado (ej. *Mariela Lopez* para el `2150`). Cuando el plugin origina una llamada click-to-call, el flujo clásico era:

```
AMI → SIP/2150 → suena el teléfono del ejecutivo
```

En ese camino **Issabel ignora el CallerID que enviamos por AMI** y el visor del teléfono muestra siempre el nombre y número del **anexo** (*Mariela Lopez 2150*), no el celular del alumno (`955170937`).

Probamos desde Laravel:

| Enfoque | Resultado |
| --- | --- |
| Variables AMI `CALLERID(name)` / `CALLERID(num)` | Issabel las pisa con el CNAM del anexo |
| Forzar `CALLERID(num)` = celular | El visor mejora pero la central responde *número equivocado* (`bad-number`) |
| Llamar directo `SIP/anexo` | El ejecutivo no ve a quién va a llamar |

La solución estándar en **FreePBX/Issabel** (documentada en sus foros de click-to-call) es:

1. Originar a un canal **Local** en un **contexto dialplan propio**.
2. Ese contexto **fija el CallerID al celular destino** *antes* de hacer `Dial(SIP/anexo)`.
3. Cuando el ejecutivo contesta, Asterisk continúa el originate hacia `from-internal/{destino}` para marcar al alumno.

Por eso el plugin usa la estrategia **`custom_agent`** y el contexto **`filament-click-to-call`**.

## Qué debe agregar el administrador de Issabel (una sola vez)

Editar en el servidor Issabel:

```text
/etc/asterisk/extensions_custom.conf
```

Pegar al final (o incluir el archivo publicado por el plugin):

```ini
[filament-click-to-call]
exten => _X.,1,NoOp(Filament click-to-call anexo ${EXTEN} destino ${CTC_DEST})
 same => n,Set(CALLERID(name)=${IF($["${CTC_NAME}"=""]?${CTC_DEST}:${CTC_NAME})})
 same => n,Set(CALLERID(num)=${IF($["${CTC_NUM}"=""]?${CTC_DEST}:${CTC_NUM})})
 same => n,Set(CALLERID(name-pres)=allowed_not_screened)
 same => n,Set(CALLERID(num-pres)=allowed_not_screened)
 same => n,Dial(SIP/${EXTEN},30,m)
 same => n,Hangup()
```

> **PJSIP:** si los anexos usan PJSIP en lugar de SIP, cambie la línea `Dial(SIP/${EXTEN},30,m)` por `Dial(PJSIP/${EXTEN},30,m)`.

Recargar dialplan:

```bash
asterisk -rx "dialplan reload"
```

## Publicar el archivo desde Laravel

```bash
php artisan vendor:publish --tag=filament-issabel-click-to-call-dialplan
```

Genera:

- `resources/issabel/filament-click-to-call.conf` — snippet para copiar a Issabel
- `docs/ISSABEL_VISOR_DESTINO.md` — esta guía en el proyecto

## Configuración `.env` (Laravel)

```env
ISSABEL_PBX_ORIGINATE_STRATEGY=custom_agent
ISSABEL_PBX_AGENT_DIAL_CONTEXT=filament-click-to-call
ISSABEL_PBX_CHANNEL_DRIVER=SIP
ISSABEL_PBX_DIAL_CONTEXT=from-internal
ISSABEL_PBX_DIAL_FORMAT=local_9
ISSABEL_PBX_CALLER_ID_DISPLAY=destination
```

## Flujo AMI con `custom_agent`

```text
Action: Originate
Channel: Local/2150@filament-click-to-call
Context: from-internal
Exten: 955170937
Variable: CTC_DEST=955170937
Variable: CTC_NAME=9 5517 0937
Variable: CTC_NUM=955170937
```

1. Entra al contexto `filament-click-to-call` → fija visor = celular → suena el anexo `2150`.
2. El ejecutivo ve **955170937** (o formateado) en el teléfono.
3. Al contestar, Issabel ejecuta `from-internal,955170937,1` y marca al alumno por la ruta saliente normal.

## Sin el contexto custom

Si **no** se instala el bloque en `extensions_custom.conf`, use temporalmente:

```env
ISSABEL_PBX_ORIGINATE_STRATEGY=application_dial
```

La llamada puede funcionar, pero el visor seguirá mostrando el CNAM del anexo (*Mariela Lopez 2150*).
