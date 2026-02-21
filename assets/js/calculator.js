(function () {
    function parseSettings(el) {
        const raw = el.getAttribute('data-calculator-settings');
        if (!raw) {
            return null;
        }
        return JSON.parse(raw);
    }

    function discountMultiplier(qty) {
        if (qty >= 30) {
            return 0.7;
        }
        if (qty >= 16) {
            return 0.8;
        }
        if (qty >= 6) {
            return 0.9;
        }
        return 1;
    }

    function buildTabs(state, tabsEl) {
        tabsEl.innerHTML = '';
        state.settings.adTypes.forEach((adType) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = adType.label;
            button.dataset.type = adType.key;
            if (adType.key === state.type) {
                button.classList.add('active');
            }

            button.addEventListener('click', () => {
                state.type = adType.key;
                tabsEl.querySelectorAll('button').forEach((item) => item.classList.remove('active'));
                button.classList.add('active');
                update(state);
            });

            tabsEl.appendChild(button);
        });
    }

    function update(state) {
        const adType = state.settings.adTypes.find((item) => item.key === state.type) || state.settings.adTypes[0];
        const currency = state.settings.currencies[state.currency];
        const qty = parseInt(state.qty.value, 10) || 5;
        const base = adType.basePriceGbp;
        const styleMultiplier = adType.styles && adType.styles[0] ? adType.styles[0].multiplier : 1;
        const tier = discountMultiplier(qty);

        const unit = Math.round(base * styleMultiplier * tier * currency.rate);
        const total = unit * qty;
        const nonDiscounted = Math.round(base * styleMultiplier * currency.rate);
        const percent = Math.round((1 - tier) * 100);
        const fill = ((qty - 5) / 45) * 100;

        state.bubble.textContent = qty;
        state.bubble.style.left = `${fill}%`;
        state.qty.style.background = `linear-gradient(to right,var(--aa-accent) ${fill}%,#ddd ${fill}%)`;

        state.adsCount.textContent = qty;
        state.unitPrice.textContent = `${currency.symbol}${unit}`;
        state.totalPrice.textContent = `${currency.symbol}${total}`;

        if (tier < 1) {
            state.discount.style.display = 'inline-block';
            state.discount.textContent = `${currency.symbol}${nonDiscounted} | ${percent}% Off`;
        } else {
            state.discount.style.display = 'none';
        }
    }

    function mountCalculator(el) {
        const settings = parseSettings(el);
        if (!settings || !settings.adTypes || !settings.adTypes.length) {
            return;
        }

        const state = {
            settings,
            type: settings.adTypes[0].key,
            currency: 'GBP',
            qty: el.querySelector('[data-role="qty"]'),
            bubble: el.querySelector('[data-role="bubble"]'),
            adsCount: el.querySelector('[data-role="ads-count"]'),
            unitPrice: el.querySelector('[data-role="unit-price"]'),
            totalPrice: el.querySelector('[data-role="total-price"]'),
            discount: el.querySelector('[data-role="discount"]'),
        };

        const tabsEl = el.querySelector('[data-role="ad-tabs"]');
        buildTabs(state, tabsEl);

        const currencySelect = el.querySelector('[data-role="currency"]');
        Object.keys(settings.currencies).forEach((code) => {
            const option = document.createElement('option');
            option.value = code;
            option.textContent = code;
            currencySelect.appendChild(option);
        });
        currencySelect.value = 'GBP';

        currencySelect.addEventListener('change', (event) => {
            state.currency = event.target.value;
            update(state);
        });

        state.qty.addEventListener('input', () => update(state));

        el.querySelector('[data-role="credits-msg"]').textContent = settings.creditsMessage || '';
        update(state);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.attentionads-calculator').forEach((el) => mountCalculator(el));
    });
})();
