/**
 * Attention Ads – Package Calculator  |  calculator.js
 *
 * Runs entirely in the browser with zero dependencies.
 * Reads configuration from the widget's data-config attribute,
 * then manages state and DOM updates for the live pricing UI.
 */
(function () {
  'use strict';

  // ---------------------------------------------------------------------------
  // Bootstrap – initialise every calculator instance on the page
  // ---------------------------------------------------------------------------
  function init() {
    document.querySelectorAll('.aac-calculator').forEach(initInstance);
  }

  /**
   * Initialise a single calculator widget.
   * @param {HTMLElement} root
   */
  function initInstance(root) {
    // Parse config embedded by PHP.
    var raw = root.getAttribute('data-config');
    if (!raw) return;

    var config;
    try {
      config = JSON.parse(raw);
    } catch (e) {
      console.error('Attention Ads Calculator: invalid config JSON', e);
      return;
    }

    // ── Widget state ──────────────────────────────────────────────────────────
    var state = {
      currency: config.defaultCurrency || 'GBP',
      blocks: [],   // Array of block state objects
      nextId: 1,
    };

    // ── DOM references ────────────────────────────────────────────────────────
    var adTypeSelect     = root.querySelector('.aac-ad-type-select');
    var addBtn           = root.querySelector('.aac-btn-add');
    var blocksContainer  = root.querySelector('.aac-blocks-container');
    var breakdownList    = root.querySelector('.aac-breakdown-list');
    var totalValue       = root.querySelector('.aac-total-value');
    var currencySelect   = root.querySelector('.aac-currency-select');

    // ── Populate ad type dropdown ─────────────────────────────────────────────
    config.adTypes.forEach(function (adType) {
      var opt = document.createElement('option');
      opt.value = adType.id;
      opt.textContent = adType.label + ' (' + adType.unit + ')';
      adTypeSelect.appendChild(opt);
    });

    // ── Event: currency change ────────────────────────────────────────────────
    if (currencySelect) {
      currencySelect.addEventListener('change', function () {
        state.currency = currencySelect.value;
        refreshAll();
      });
    }

    // ── Event: Add to Package ─────────────────────────────────────────────────
    addBtn.addEventListener('click', function () {
      var typeId = adTypeSelect.value;
      if (!typeId) return;

      var adType = findAdType(typeId);
      if (!adType) return;

      var blockState = {
        id: state.nextId++,
        adTypeId: adType.id,
        label: adType.label,
        unit: adType.unit,
        basePrice: adType.basePrice,
        styles: adType.styles,
        selectedStyle: adType.styles[0].id,
        quantity: 1,
        revisions: false,
        revisionCount: 1,
      };

      state.blocks.push(blockState);
      renderBlock(blockState);
      refreshAll();

      // Reset selector.
      adTypeSelect.value = '';
    });

    // ── Helpers ───────────────────────────────────────────────────────────────

    function findAdType(id) {
      return config.adTypes.find(function (t) { return t.id === id; }) || null;
    }

    /**
     * Calculate the unit price (in GBP) for a block, applying style multiplier
     * and bulk discount.
     */
    function calcUnitPrice(block) {
      var style = block.styles.find(function (s) { return s.id === block.selectedStyle; });
      var multiplier = style ? style.multiplier : 1;
      var discount = getDiscount(block.quantity);
      return block.basePrice * multiplier * (1 - discount / 100);
    }

    /**
     * Calculate total subtotal for a block (GBP) including revisions.
     */
    function calcSubtotal(block) {
      var unit = calcUnitPrice(block);
      var base = unit * block.quantity;
      var revisionCost = 0;
      if (block.revisions && block.revisionCount > 0) {
        revisionCost = config.revisionPrice * block.quantity * block.revisionCount;
      }
      return base + revisionCost;
    }

    /**
     * Return the discount percentage for a given quantity.
     */
    function getDiscount(qty) {
      for (var i = 0; i < config.discountTiers.length; i++) {
        if (qty <= config.discountTiers[i].max) {
          return config.discountTiers[i].percent;
        }
      }
      return 0;
    }

    /**
     * Convert a GBP amount to the currently selected currency.
     */
    function convertPrice(gbp) {
      var info = config.currencies[state.currency];
      return info ? gbp * info.rate : gbp;
    }

    /**
     * Format a price for display.
     */
    function formatPrice(gbp) {
      var info = config.currencies[state.currency] || config.currencies['GBP'];
      var amount = gbp * info.rate;
      return info.symbol + amount.toFixed(2);
    }

    // ── Render a new ad block ─────────────────────────────────────────────────

    function renderBlock(block) {
      var el = document.createElement('div');
      el.className = 'aac-ad-block';
      el.setAttribute('data-block-id', block.id);

      el.innerHTML = buildBlockHTML(block);
      blocksContainer.appendChild(el);

      wireBlockEvents(el, block);
    }

    function buildBlockHTML(block) {
      var styleButtons = block.styles.map(function (s) {
        var active = s.id === block.selectedStyle ? ' is-active' : '';
        return '<button type="button" class="aac-style-btn' + active + '" data-style="' + esc(s.id) + '">' + esc(s.label) + '</button>';
      }).join('');

      return (
        '<div class="aac-ad-block-header">' +
          '<h4 class="aac-ad-block-title">' + esc(block.label) + ' <span class="aac-ad-block-unit">(' + esc(block.unit) + ')</span></h4>' +
          '<button type="button" class="aac-btn-remove" aria-label="Remove ' + esc(block.label) + '">&#x2715;</button>' +
        '</div>' +
        '<div class="aac-ad-block-body">' +
          // Style / complexity
          '<div class="aac-field-group">' +
            '<span class="aac-field-label">Style / Complexity</span>' +
            '<div class="aac-style-options">' + styleButtons + '</div>' +
          '</div>' +
          // Quantity
          '<div class="aac-field-group">' +
            '<span class="aac-field-label">Quantity</span>' +
            '<div class="aac-quantity-row">' +
              '<input type="range" class="aac-slider-range" min="1" max="50" value="' + block.quantity + '" aria-label="Quantity slider">' +
              '<input type="number" class="aac-quantity-input" min="1" max="9999" value="' + block.quantity + '" aria-label="Quantity">' +
            '</div>' +
          '</div>' +
          // Revisions
          '<div class="aac-field-group">' +
            '<span class="aac-field-label">Revisions</span>' +
            '<div class="aac-revisions-row">' +
              '<label class="aac-revisions-toggle">' +
                '<input type="checkbox" class="aac-revisions-checkbox">' +
                'Add revisions (+' + esc(config.currencies[state.currency] ? config.currencies[state.currency].symbol : '£') + (config.revisionPrice * (config.currencies[state.currency] ? config.currencies[state.currency].rate : 1)).toFixed(0) + ' per asset per revision)' +
              '</label>' +
              '<div class="aac-revisions-count-wrap">' +
                '<span style="font-size:0.82rem;color:var(--aac-text-muted)">Revisions:</span>' +
                '<input type="number" class="aac-revisions-input" min="1" max="20" value="1" aria-label="Number of revisions">' +
              '</div>' +
            '</div>' +
          '</div>' +
          // Price preview
          '<div class="aac-price-preview">' +
            '<div class="aac-price-preview-meta">' +
              '<div class="aac-price-per-unit">Price per unit: <strong class="js-unit-price">—</strong></div>' +
              '<span class="aac-discount-badge js-discount-badge"></span>' +
            '</div>' +
            '<div class="aac-subtotal">' +
              '<span class="aac-subtotal-label">Subtotal</span>' +
              '<span class="aac-subtotal-value js-subtotal">—</span>' +
            '</div>' +
          '</div>' +
        '</div>'
      );
    }

    function wireBlockEvents(el, block) {
      // Style buttons.
      el.querySelectorAll('.aac-style-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
          block.selectedStyle = btn.getAttribute('data-style');
          el.querySelectorAll('.aac-style-btn').forEach(function (b) {
            b.classList.toggle('is-active', b === btn);
          });
          updateBlockPriceDisplay(el, block);
          refreshSummary();
        });
      });

      // Quantity slider & input (keep in sync).
      var slider = el.querySelector('.aac-slider-range');
      var numInput = el.querySelector('.aac-quantity-input');

      slider.addEventListener('input', function () {
        block.quantity = parseInt(slider.value, 10) || 1;
        numInput.value = block.quantity;
        updateBlockPriceDisplay(el, block);
        refreshSummary();
      });

      numInput.addEventListener('input', function () {
        var val = parseInt(numInput.value, 10) || 1;
        block.quantity = val;
        // Keep slider in sync (clamped to slider max).
        slider.value = Math.min(val, parseInt(slider.max, 10));
        updateBlockPriceDisplay(el, block);
        refreshSummary();
      });

      // Revisions checkbox.
      var revCheckbox = el.querySelector('.aac-revisions-checkbox');
      var revCountWrap = el.querySelector('.aac-revisions-count-wrap');
      var revInput = el.querySelector('.aac-revisions-input');

      revCheckbox.addEventListener('change', function () {
        block.revisions = revCheckbox.checked;
        revCountWrap.classList.toggle('is-visible', block.revisions);
        updateBlockPriceDisplay(el, block);
        refreshSummary();
      });

      revInput.addEventListener('input', function () {
        block.revisionCount = parseInt(revInput.value, 10) || 1;
        updateBlockPriceDisplay(el, block);
        refreshSummary();
      });

      // Remove button.
      el.querySelector('.aac-btn-remove').addEventListener('click', function () {
        state.blocks = state.blocks.filter(function (b) { return b.id !== block.id; });
        el.remove();
        refreshSummary();
      });

      // Initial display.
      updateBlockPriceDisplay(el, block);
    }

    /**
     * Update the price-per-unit, discount badge, and subtotal for one block.
     */
    function updateBlockPriceDisplay(el, block) {
      var unitGBP     = calcUnitPrice(block);
      var subtotalGBP = calcSubtotal(block);
      var discount    = getDiscount(block.quantity);

      el.querySelector('.js-unit-price').textContent = formatPrice(unitGBP);

      var badge = el.querySelector('.js-discount-badge');
      if (discount > 0) {
        badge.textContent = discount + '% bulk discount applied!';
        badge.style.display = 'inline-flex';
      } else {
        badge.textContent = '';
        badge.style.display = 'none';
      }

      el.querySelector('.js-subtotal').textContent = formatPrice(subtotalGBP);
    }

    // ── Summary / total ───────────────────────────────────────────────────────

    function refreshSummary() {
      // Clear existing items.
      breakdownList.innerHTML = '';

      if (state.blocks.length === 0) {
        var empty = document.createElement('li');
        empty.className = 'aac-breakdown-empty';
        empty.textContent = 'No ad types added yet.';
        breakdownList.appendChild(empty);
        totalValue.textContent = '—';
        return;
      }

      var grandTotal = 0;

      state.blocks.forEach(function (block) {
        var sub = calcSubtotal(block);
        grandTotal += sub;

        var li = document.createElement('li');
        li.className = 'aac-breakdown-item';

        var style = block.styles.find(function (s) { return s.id === block.selectedStyle; });
        var styleName = style ? style.label : '';
        var revNote = block.revisions ? ' + ' + block.revisionCount + ' revision(s)' : '';

        li.innerHTML =
          '<span class="aac-breakdown-item-label">' +
            esc(block.label) + ' &times; ' + block.quantity +
            ' <small>(' + esc(styleName) + revNote + ')</small>' +
          '</span>' +
          '<span class="aac-breakdown-item-price">' + formatPrice(sub) + '</span>';

        breakdownList.appendChild(li);
      });

      totalValue.textContent = formatPrice(grandTotal);
    }

    /**
     * Full refresh: update all block displays + summary (called on currency change).
     */
    function refreshAll() {
      state.blocks.forEach(function (block) {
        var el = blocksContainer.querySelector('[data-block-id="' + block.id + '"]');
        if (el) {
          // Rebuild revision label with updated currency symbol.
          var revLabel = el.querySelector('.aac-revisions-toggle');
          if (revLabel) {
            var info = config.currencies[state.currency] || { symbol: '£', rate: 1 };
            var priceFormatted = info.symbol + (config.revisionPrice * info.rate).toFixed(0);
            revLabel.childNodes.forEach(function (node) {
              if (node.nodeType === Node.TEXT_NODE && node.textContent.includes('revision')) {
                node.textContent = 'Add revisions (+' + priceFormatted + ' per asset per revision)';
              }
            });
          }
          updateBlockPriceDisplay(el, block);
        }
      });
      refreshSummary();
    }

    // ── Kick off ──────────────────────────────────────────────────────────────
    refreshSummary();
  }

  // ---------------------------------------------------------------------------
  // Utility
  // ---------------------------------------------------------------------------

  /** Escape HTML special characters to prevent XSS. */
  function esc(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // ---------------------------------------------------------------------------
  // Entry point – handle both page-load and Elementor editor live preview
  // ---------------------------------------------------------------------------
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Elementor editor: re-init after widget renders in preview.
  if (window.elementorFrontend) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/attention_ads_calculator.default', function ($scope) {
      initInstance($scope[0]);
    });
  }
})();
