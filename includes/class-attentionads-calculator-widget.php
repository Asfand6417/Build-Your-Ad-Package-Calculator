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
                ['key' => 'ugc_video', 'label' => 'UGC Video', 'unit_label' => 'per video', 'base_price_gbp' => 200, 'styles' => "Standard UGC|1\nCreator-led / scripted|1.1\nPerformance-optimised / hooks + variations|1.25"],
                ['key' => 'motion_ad', 'label' => 'Motion Ad', 'unit_label' => 'per asset', 'base_price_gbp' => 400, 'styles' => "Basic animation|1\nAdvanced motion / transitions|1.2"],
                ['key' => 'graphic_ad', 'label' => 'Graphic Ad', 'unit_label' => 'per asset', 'base_price_gbp' => 150, 'styles' => "Standard|1"],
                ['key' => 'video_edit', 'label' => 'Video Edit', 'unit_label' => 'per edit', 'base_price_gbp' => 180, 'styles' => "Standard|1"],
                ['key' => 'creative_brief', 'label' => 'Creative Brief', 'unit_label' => 'per brief', 'base_price_gbp' => 120, 'styles' => "Standard|1"],
                ['key' => 'out_of_home', 'label' => 'Out of Home', 'unit_label' => 'per asset', 'base_price_gbp' => 350, 'styles' => "Standard|1"],
                ['key' => 'audio_for_ads', 'label' => 'Audio for Ads', 'unit_label' => 'per asset', 'base_price_gbp' => 1000, 'styles' => "Standard|1"],
            ],
        ]);

        $this->add_control('revision_fee_gbp', [
            'label' => __('Reversion Fee per Asset (GBP)', 'attentionads'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 30,
            'min' => 0,
        ]);

        $this->add_control('credits_message', [
            'label' => __('Credits Message', 'attentionads'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'Credits never expire and can be used flexibly.',
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
            'revisionFeeGbp' => (float) $settings['revision_fee_gbp'],
            'currencies' => [
                'GBP' => ['symbol' => '£', 'rate' => 1],
                'USD' => ['symbol' => '$', 'rate' => 1.28],
                'EUR' => ['symbol' => '€', 'rate' => 1.17],
                'AED' => ['symbol' => 'AED ', 'rate' => 4.70],
                'AUD' => ['symbol' => 'A$', 'rate' => 1.94],
            ],
            'bulkTiers' => [
                ['min' => 1, 'max' => 5, 'multiplier' => 1, 'label' => 'Base pricing'],
                ['min' => 6, 'max' => 15, 'multiplier' => 0.9, 'label' => '10% bulk discount applied'],
                ['min' => 16, 'max' => 30, 'multiplier' => 0.8, 'label' => '20% bulk discount applied'],
                ['min' => 31, 'max' => null, 'multiplier' => 0.7, 'label' => '30% bulk discount applied'],
            ],
            'creditsMessage' => sanitize_text_field($settings['credits_message']),
        ];
        ?>
        <div class="attentionads-calculator" data-calculator-settings='<?php echo wp_json_encode($widget_settings); ?>'>
            <div class="attentionads-head">
                <h3><?php esc_html_e('Build Your Ad Package', 'attentionads'); ?></h3>
                <div class="attentionads-controls">
                    <label>
                        <?php esc_html_e('Currency', 'attentionads'); ?>
                        <select data-role="currency"></select>
                    </label>
                    <button type="button" data-role="add-item"><?php esc_html_e('+ Add Ad Type', 'attentionads'); ?></button>
                </div>
            </div>

            <div class="attentionads-items" data-role="items"></div>

            <div class="attentionads-summary">
                <h4><?php esc_html_e('Package Breakdown', 'attentionads'); ?></h4>
                <div data-role="breakdown"></div>
                <p class="attentionads-total"><?php esc_html_e('Total:', 'attentionads'); ?> <span data-role="grand-total">£0.00</span></p>
                <p class="attentionads-credits" data-role="credits-msg"></p>
            </div>
        </div>
        <?php
    }
}
