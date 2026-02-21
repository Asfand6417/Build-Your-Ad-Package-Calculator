(function () {
    function parseSettings(el) {
        const raw = el.getAttribute('data-calculator-settings');
        if (!raw) {
            return null;
        }
        return JSON.parse(raw);
    }

    function money(value, currency) {
        return `${currency.symbol}${value.toFixed(2)}`;
    }

    function getBulkTier(qty, tiers) {
        return tiers.find((tier) => qty >= tier.min && (tier.max === null || qty <= tier.max)) || tiers[0];
    }

    function makeItemRow(state, itemIndex) {
        const item = state.items[itemIndex];
        const ad = state.settings.adTypes.find((x) => x.key === item.adKey) || state.settings.adTypes[0];
        const styles = ad.styles || [{ label: 'Standard', multiplier: 1 }];

        const wrap = document.createElement('div');
        wrap.className = 'attentionads-item';
        wrap.innerHTML = `
            <div class="attentionads-grid">
                <label>Ad Type
                    <select data-field="adKey">
                        ${state.settings.adTypes.map((adType) => `<option value="${adType.key}" ${adType.key === ad.key ? 'selected' : ''}>${adType.label}</option>`).join('')}
                    </select>
                </label>
                <label>Style
                    <select data-field="styleIndex">
                        ${styles.map((style, idx) => `<option value="${idx}" ${idx === item.styleIndex ? 'selected' : ''}>${style.label}</option>`).join('')}
                    </select>
                </label>
                <label>Quantity
                    <input data-field="quantity" type="number" min="1" value="${item.quantity}" />
                </label>
                <label>Reversions
                    <input data-field="reversions" type="number" min="0" value="${item.reversions}" />
                </label>
            </div>
            <div class="attentionads-meta">
                <p data-role="bulk-note"></p>
                <p>${ad.unitLabel}</p>
                <p>Price per unit: <span data-role="unit-price"></span></p>
                <p>Subtotal: <span data-role="subtotal"></span></p>
            </div>
            <button type="button" data-role="remove">Remove</button>
        `;

        const bulkNote = wrap.querySelector('[data-role="bulk-note"]');
        const unitPriceEl = wrap.querySelector('[data-role="unit-price"]');
        const subtotalEl = wrap.querySelector('[data-role="subtotal"]');

        function recalcAndPaint() {
            const selectedAd = state.settings.adTypes.find((x) => x.key === item.adKey) || state.settings.adTypes[0];
            const selectedStyles = selectedAd.styles || [{ label: 'Standard', multiplier: 1 }];
            const safeStyle = selectedStyles[item.styleIndex] || selectedStyles[0];
            const tier = getBulkTier(item.quantity, state.settings.bulkTiers);
            const currency = state.settings.currencies[state.currency];

            const unitGbp = selectedAd.basePriceGbp * safeStyle.multiplier * tier.multiplier;
            const subtotalGbp = unitGbp * item.quantity;
            const revisionsGbp = state.settings.revisionFeeGbp * item.reversions;

            item.calculation = {
                adLabel: selectedAd.label,
                unitLabel: selectedAd.unitLabel,
                tierLabel: tier.label,
                unitGbp,
                subtotalGbp,
                revisionsGbp,
                totalGbp: subtotalGbp + revisionsGbp,
            };

            bulkNote.textContent = tier.label;
            unitPriceEl.textContent = money(unitGbp * currency.rate, currency);
            subtotalEl.textContent = money((subtotalGbp + revisionsGbp) * currency.rate, currency);
        }

        wrap.addEventListener('change', (event) => {
            const field = event.target.getAttribute('data-field');
            if (!field) {
                return;
            }

            if (field === 'adKey') {
                item.adKey = event.target.value;
                item.styleIndex = 0;
                redraw(state);
                return;
            }

            if (field === 'styleIndex') {
                item.styleIndex = parseInt(event.target.value, 10) || 0;
            }

            if (field === 'quantity') {
                item.quantity = Math.max(1, parseInt(event.target.value, 10) || 1);
            }

            if (field === 'reversions') {
                item.reversions = Math.max(0, parseInt(event.target.value, 10) || 0);
            }

            recalcAndPaint();
            paintSummary(state);
        });

        wrap.querySelector('[data-role="remove"]').addEventListener('click', () => {
            state.items.splice(itemIndex, 1);
            redraw(state);
        });

        recalcAndPaint();
        return wrap;
    }

    function paintSummary(state) {
        const breakdown = state.el.querySelector('[data-role="breakdown"]');
        const total = state.el.querySelector('[data-role="grand-total"]');
        const currency = state.settings.currencies[state.currency];

        let html = '';
        let grandGbp = 0;

        state.items.forEach((item) => {
            if (!item.calculation) {
                return;
            }
            grandGbp += item.calculation.totalGbp;
            html += `<div class="attentionads-breakdown-row">
                <strong>${item.calculation.adLabel}</strong>
                <span>${item.quantity} × ${money(item.calculation.unitGbp * currency.rate, currency)}</span>
                <span>${money(item.calculation.totalGbp * currency.rate, currency)}</span>
            </div>`;
        });

        if (!html) {
            html = '<p>Add an ad type to start building your package.</p>';
        }

        breakdown.innerHTML = html;
        total.textContent = money(grandGbp * currency.rate, currency);
    }

    function redraw(state) {
        const itemsWrap = state.el.querySelector('[data-role="items"]');
        itemsWrap.innerHTML = '';
        state.items.forEach((_, idx) => {
            itemsWrap.appendChild(makeItemRow(state, idx));
        });
        paintSummary(state);
    }

    function mountCalculator(el) {
        const settings = parseSettings(el);
        if (!settings || !settings.adTypes || !settings.adTypes.length) {
            return;
        }

        const state = {
            el,
            settings,
            currency: 'GBP',
            items: [{ adKey: settings.adTypes[0].key, quantity: 1, styleIndex: 0, reversions: 0 }],
        };

        const currencySelect = el.querySelector('[data-role="currency"]');
        Object.keys(settings.currencies).forEach((code) => {
            const option = document.createElement('option');
            option.value = code;
            option.textContent = code;
            currencySelect.appendChild(option);
        });

        currencySelect.value = 'GBP';
        currencySelect.addEventListener('change', () => {
            state.currency = currencySelect.value;
            redraw(state);
        });

        el.querySelector('[data-role="add-item"]').addEventListener('click', () => {
            state.items.push({ adKey: settings.adTypes[0].key, quantity: 1, styleIndex: 0, reversions: 0 });
            redraw(state);
        });

        el.querySelector('[data-role="credits-msg"]').textContent = settings.creditsMessage || '';

        redraw(state);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.attentionads-calculator').forEach((el) => mountCalculator(el));
    });
})();
