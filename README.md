# Build-Your-Ad-Package-Calculator

Elementor-ready widget for a modular ad package calculator with:
- Multiple ad types in one package
- Bulk discount tiers
- Style/complexity multipliers
- Reversion pricing per asset
- Multi-currency output (GBP, USD, EUR, AED, AUD)

## Files
- `elementor-attentionads-calculator.php` plugin bootstrap + widget registration
- `includes/class-attentionads-calculator-widget.php` Elementor widget controls + render markup
- `assets/js/calculator.js` dynamic calculator logic
- `assets/css/calculator.css` UI styling

## Step-by-step (for non-technical Elementor users)
1. Install this folder as a WordPress plugin and activate it.
2. Open a page in Elementor.
3. Search widget: **Attention Ads Calculator**.
4. Drag the widget into your section.
5. In widget settings:
   - Edit **Ad Types** in the repeater.
   - Set each base price in GBP.
   - Set style lines as `Label|Multiplier`.
   - Set reversion fee (default £30).
   - Update credits message.
6. Publish the page.

## Pricing logic included
For each ad type block:
- Unit price = `base price × style multiplier × bulk tier multiplier`
- Bulk tiers:
  - 1–5: `1.0`
  - 6–15: `0.9`
  - 16–30: `0.8`
  - 31+: `0.7`
- Item subtotal = `(unit price × quantity) + (reversion fee × reversions)`
- Grand total = sum of all item subtotals

## Notes
- Currency exchange is driven by static rates in widget render settings and can be adjusted in PHP.
- Each ad type calculates independently and rolls up into one package total.
