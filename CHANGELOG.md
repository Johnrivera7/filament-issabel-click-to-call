# Changelog

## 1.1.1 — 2026-09-08

- `isExtensionInCall()` / `activeChannels()` now throw when AMI is unconfigured or
  `CoreShowChannels` is rejected, instead of reporting the extension as idle
- `CoreShowChannels` responses are validated before reading the event list, so a
  denied action no longer blocks until the socket read timeout

## 1.1.0 — 2026-09-08

- **Extra call legs prevented** — before Originate the plugin checks the live extension
  state (`ExtensionState` hint, `CoreShowChannels` fallback) and holds an atomic lock
  per extension, so ringing/talking extensions cannot be dialled a second time
- `ClickToCallService::extensionInCall()` and `IssabelAmiGateway::extensionState()`,
  `activeChannels()`, `isExtensionInCall()` to query live PBX state
- New config: `prevent_extra_legs`, `originate_lock_seconds`, `hint_context`
- AMI sessions now always log out and close the socket, even when an action fails
- Dialplan snippet: destination-first mode (`filament-ctc-dest-first`,
  `filament-ctc-bridge-agents`) with failover to backup agents and supervisor

## 1.0.0 — 2026-08-24

- Initial release: AMI originate click-to-call via Asterisk Manager
- `ClickToCallAction` for Filament tables/forms
- Settings page for AMI credentials
- Chile phone normalization helper
- EN/ES translations
- **`custom_agent` originate strategy** — agent phone shows customer number via Issabel dialplan context `filament-click-to-call`
- Publish tag `filament-issabel-click-to-call-dialplan` for `extensions_custom.conf` snippet and setup guide
