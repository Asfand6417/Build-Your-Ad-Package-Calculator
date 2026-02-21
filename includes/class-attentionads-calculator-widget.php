<?php

if (!defined('ABSPATH')) {
    exit;
}

class AttentionAds_Calculator_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'attentionads_calculator';
    }

    public function get_title() {
        return __('Attention Ads Calculator', 'attentionads');
    }

    public function get_icon() {
        return 'eicon-price-table';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_script_depends() {
        return ['attentionads-calculator'];
    }

    public function get_style_depends() {
        return ['attentionads-calculator'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Calculator Settings', 'attentionads'),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control('key', [
            'label' => __('Ad Key', 'attentionads'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'ugc_video',
            'description' => __('Unique slug for internal logic.', 'attentionads'),
        ]);

        $repeater->add_control('label', [
            'label' => __('Ad Type Label', 'attentionads'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'UGC Video',
        ]);

        $repeater->add_control('unit_label', [
            'label' => __('Unit Label', 'attentionads'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'per video',
        ]);

        $repeater->add_control('base_price_gbp', [
            'label' => __('Base Price (GBP)', 'attentionads'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 200,
            'min' => 0,
        ]);

        $repeater->add_control('styles', [
            'label' => __('Style Options', 'attentionads'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => "Standard|1\nAdvanced|1.2",
            'description' => __('One per line: Label|Multiplier. Example: Performance-Optimised|1.25', 'attentionads'),
        ]);

        $this->add_control('ad_types', [
            'label' => __('Ad Types', 'attentionads'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'title_field' => '{{{ label }}}',
            'default' => [
                ['key' => 'creator', 'label' => 'Creator Ads', 'unit_label' => 'per ad', 'base_price_gbp' => 350, 'styles' => "Standard|1"],
                ['key' => 'graphic', 'label' => 'Graphic Ads', 'unit_label' => 'per ad', 'base_price_gbp' => 150, 'styles' => "Standard|1"],
                ['key' => 'motion', 'label' => 'Motion Ads', 'unit_label' => 'per ad', 'base_price_gbp' => 400, 'styles' => "Standard|1"],
                ['key' => 'ooh', 'label' => 'Out of Home (OOH)', 'unit_label' => 'per ad', 'base_price_gbp' => 350, 'styles' => "Standard|1"],
            ],
        ]);

        $this->add_control('credits_message', [
            'label' => __('Credits Message', 'attentionads'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'Credits never expire. Use them whenever you\'re ready.',
        ]);

        $this->add_control('bulk_message', [
            'label' => __('Bulk Saving Message', 'attentionads'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => 'Purchase in bulk and <strong>save up to 30%</strong>. Use up your ad credits anytime, credits never expire.',
        ]);

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'attentionads'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control('container_background', [
            'label' => __('Container Background', 'attentionads'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#f6eef9',
            'selectors' => [
                '{{WRAPPER}} .attentionads-calculator' => '--aa-container-bg: {{VALUE}}',
            ],
        ]);

        $this->add_control('card_background', [
            'label' => __('Card Background', 'attentionads'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .attentionads-calculator' => '--aa-card-bg: {{VALUE}}',
            ],
        ]);

        $this->add_control('accent_color', [
            'label' => __('Accent Color', 'attentionads'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#7c5cff',
            'selectors' => [
                '{{WRAPPER}} .attentionads-calculator' => '--aa-accent: {{VALUE}}',
            ],
        ]);

        $this->add_control('button_background', [
            'label' => __('Primary Button Background', 'attentionads'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#000000',
            'selectors' => [
                '{{WRAPPER}} .attentionads-calculator' => '--aa-button-bg: {{VALUE}}',
            ],
        ]);

        $this->add_control('button_text_color', [
            'label' => __('Primary Button Text', 'attentionads'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .attentionads-calculator' => '--aa-button-text: {{VALUE}}',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $ad_types = [];
        foreach ($settings['ad_types'] as $ad_type) {
            $styles = [];
            $rows = preg_split('/\r\n|\r|\n/', trim((string) $ad_type['styles']));
            foreach ($rows as $row) {
                $parts = array_map('trim', explode('|', $row));
                if (count($parts) < 2) {
                    continue;
                }

                $styles[] = [
                    'label' => $parts[0],
                    'multiplier' => (float) $parts[1],
                ];
            }

            if (empty($styles)) {
                $styles[] = [
                    'label' => 'Standard',
                    'multiplier' => 1,
                ];
            }

            $ad_types[] = [
                'key' => sanitize_key($ad_type['key']),
                'label' => sanitize_text_field($ad_type['label']),
                'unitLabel' => sanitize_text_field($ad_type['unit_label']),
                'basePriceGbp' => (float) $ad_type['base_price_gbp'],
                'styles' => $styles,
            ];
        }

        $widget_settings = [
            'adTypes' => $ad_types,
            'currencies' => [
                'GBP' => ['symbol' => '£', 'rate' => 1],
                'USD' => ['symbol' => '$', 'rate' => 1.27],
                'EUR' => ['symbol' => '€', 'rate' => 1.17],
                'AED' => ['symbol' => 'د.إ', 'rate' => 4.66],
                'AUD' => ['symbol' => 'A$', 'rate' => 1.95],
            ],
            'creditsMessage' => sanitize_text_field($settings['credits_message']),
        ];
        ?>
        <div class="attentionads-calculator" data-calculator-settings='<?php echo wp_json_encode($widget_settings); ?>'>
            <div class="aa-grid">
                <div class="aa-left">
                    <h2><?php esc_html_e('Build Your', 'attentionads'); ?><br><?php esc_html_e('Ad Package', 'attentionads'); ?></h2>
                    <p class="aa-bulk"><?php echo wp_kses_post($settings['bulk_message']); ?></p>
                </div>

                <div class="aa-right">
                    <div class="aa-top">
                        <div class="aa-types">
                            <label><?php esc_html_e('Select Ad Type', 'attentionads'); ?></label>
                            <div class="aa-tabs" data-role="ad-tabs"></div>
                        </div>

                        <div class="aa-currency-wrap">
                            <label><?php esc_html_e('Choose Currency', 'attentionads'); ?></label>
                            <select data-role="currency"></select>
                        </div>
                    </div>

                    <div class="aa-slider-wrap">
                        <label><?php esc_html_e('How many ads do you need?', 'attentionads'); ?></label>
                        <div class="aa-slider">
                            <input type="range" min="5" max="50" step="5" value="20" data-role="qty">
                            <div class="aa-bubble" data-role="bubble">20</div>
                        </div>
                        <div class="aa-marks">
                            <span>5</span>
                            <span>10</span>
                            <span>25</span>
                            <span>50</span>
                        </div>
                    </div>

                    <div class="aa-cards">
                        <div class="aa-card">
                            <small><?php esc_html_e('Number of ads', 'attentionads'); ?></small>
                            <strong data-role="ads-count">20</strong>
                        </div>

                        <div class="aa-card">
                            <small><?php esc_html_e('Price per Ad', 'attentionads'); ?></small>
                            <strong data-role="unit-price">£0</strong>
                            <div class="aa-discount" data-role="discount"></div>
                        </div>

                        <div class="aa-card">
                            <small><?php esc_html_e('Total Price', 'attentionads'); ?></small>
                            <strong data-role="total-price">£0</strong>
                        </div>
                    </div>

                    <div class="aa-footer">
                        <p data-role="credits-msg"></p>
                        <button type="button"><?php esc_html_e('Get Started', 'attentionads'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
