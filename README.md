# Build-Your-Ad-Package-Calculator

A WordPress / Elementor plugin that adds a fully interactive **Ad Package Calculator** widget. Users can mix and match ad types, adjust quantities, choose style complexity, add revisions and see a live grand-total updated in real time.

---

## Features

| Feature | Details |
|---------|---------|
| **7 ad types** | UGC Video, Motion Ad, Graphic Ad, Video Edit, Creative Brief, Out of Home, Audio for Ads |
| **Bulk discount tiers** | 1-5 → 0 %, 6-15 → 10 %, 16-30 → 20 %, 30+ → 30 % (all tiers configurable in Elementor) |
| **Style / complexity modifiers** | Per-type multipliers (e.g. Standard 1.0×, Advanced 1.2×) |
| **Revisions add-on** | Optional per-block toggle; configurable price per revision asset |
| **Multi-currency** | GBP, USD, EUR, AED, AUD — live conversion, switcher can be shown/hidden |
| **Multi-ad-type package** | Add as many ad-type blocks as needed; itemised breakdown + grand total |
| **Credits messaging** | Customisable "credits never expire" footer message |
| **Elementor controls** | All colours, typography, prices and tiers editable without code |

---

## Requirements

- WordPress 5.8+
- PHP 7.4+
- [Elementor](https://elementor.com/) 3.0+ (free or Pro)

---

## Installation

1. Download or clone this repository.
2. Copy the **`attention-ads-calculator/`** folder into your WordPress `/wp-content/plugins/` directory.
3. Log in to your WordPress dashboard → **Plugins → Installed Plugins**.
4. Activate **Attention Ads – Package Calculator**.

---

## Usage

1. Open any page in the **Elementor editor**.
2. In the widget panel search for **"Ad Package Calculator"** (under the *General* category).
3. Drag it onto your canvas.
4. Use the **Content** tab to:
   - Set the calculator title and subtitle.
   - Choose the default currency and toggle the currency switcher.
   - Set the revision price (GBP).
   - Customise the bulk-discount tier thresholds and percentages.
   - Edit the credits footer message.
5. Use the **Style** tab to adjust colours and typography.
6. **Save** the page — the calculator is fully interactive on the front end.

---

## Plugin File Structure

```
attention-ads-calculator/
├── attention-ads-calculator.php        ← Main plugin file (bootstraps everything)
├── widgets/
│   └── class-ad-calculator-widget.php ← Elementor widget (controls + render)
└── assets/
    ├── css/
    │   └── calculator.css              ← All front-end styles
    └── js/
        └── calculator.js               ← Pricing logic + DOM interactions
```

---

## Pricing Logic

### Base prices (GBP)

| Ad Type | Unit | Base Price |
|---------|------|-----------|
| UGC Video | per video | £200 |
| Motion Ad | per asset | £400 |
| Graphic Ad | per asset | £150 |
| Video Edit | per edit | £180 |
| Creative Brief | per brief | £120 |
| Out of Home | per asset | £350 |
| Audio for Ads | per asset | £1,000 |

### Bulk discount tiers (defaults)

| Quantity | Discount |
|----------|----------|
| 1–5 | 0 % |
| 6–15 | 10 % |
| 16–30 | 20 % |
| 31+ | 30 % |

### Final unit price formula

```
unit price = base price × style multiplier × (1 − bulk discount %)
subtotal   = unit price × quantity + (revision price × quantity × revision count)
```

### Currency rates (relative to GBP)

| Currency | Rate |
|----------|------|
| GBP | 1.00 |
| USD | 1.27 |
| EUR | 1.17 |
| AED | 4.67 |
| AUD | 1.96 |

---

## Customisation

All ad-type base prices, style multipliers, and currency rates are defined in
`widgets/class-ad-calculator-widget.php` in the `get_ad_types()` and
`get_currency_map()` methods — easy to edit without touching any front-end code.

Discount tier thresholds and percentages are exposed as **Elementor controls**
and can be changed directly in the editor without touching code.

---

## License

GPL-2.0-or-later — see [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)
