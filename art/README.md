# Issabel brand art — Filament directory

Colors inspired by [Issabel](https://www.issabel.com/) logo dots:

| Token | Hex |
| --- | --- |
| Cyan | `#00B4D8` |
| Orange | `#FF6B35` |
| Pink | `#E91E8C` |
| Purple | `#7B2CBF` |
| Yellow | `#FFD60A` |

Logo: simplified SVG in `brand/issabel-logo.svg` (official: [logo-issabel.svg](https://www.issabel.com/wp-content/uploads/2023/12/logo-issabel.svg)).

## Files

| File | Size | Filament field |
| --- | --- | --- |
| `banner.jpg` | 2560×1440 | Image (required) |
| `thumbnail.jpg` | 1920×1080 | Thumbnail (optional) |

Regenerate (Chrome headless):

```bash
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
  --headless=new --window-size=2560,1440 \
  --screenshot=art/banner.jpg "file://$PWD/art/banner.html"

"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
  --headless=new --window-size=1920,1080 \
  --screenshot=art/thumbnail.jpg "file://$PWD/art/thumbnail.html"
```
