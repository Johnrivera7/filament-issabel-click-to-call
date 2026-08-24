# Visor del teléfono: mostrar celular destino (no CNAM del anexo)

Issabel/FreePBX **sobrescribe** el CallerID con el nombre del anexo (ej. "Mariela Lopez 2150") si se llama directo a `SIP/2150`. Las variables AMI solas no bastan.

## Solución (una vez en el servidor Issabel)

1. Editar `/etc/asterisk/extensions_custom.conf` y pegar el contenido de:

   `vendor/johnrivera7/filament-issabel-click-to-call/resources/issabel/filament-click-to-call.conf`

2. Si los anexos usan **PJSIP**, cambiar `Dial(SIP/...)` por `Dial(PJSIP/...)`.

3. Recargar dialplan:

   ```bash
   asterisk -rx "dialplan reload"
   ```

4. En `.env` de Laravel:

   ```env
   ISSABEL_PBX_ORIGINATE_STRATEGY=custom_agent
   ISSABEL_PBX_AGENT_DIAL_CONTEXT=filament-click-to-call
   ISSABEL_PBX_CHANNEL_DRIVER=SIP
   ```

## Cómo funciona

```
AMI Originate
  Channel: Local/2150@filament-click-to-call   ← contexto custom fija visor
  Context: from-internal
  Exten: 955170937                             ← marcado saliente al celular
  Variable: CTC_DEST=955170937
  Variable: CTC_NAME=9 5517 0937
```

El ejecutivo ve **955170937** (o formateado) en el visor; al contestar Issabel marca el celular por la ruta saliente normal.
