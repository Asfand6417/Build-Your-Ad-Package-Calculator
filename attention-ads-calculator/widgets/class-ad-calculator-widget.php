<?php
/**
 * Elementor Ad Calculator Widget.
 *
 * @package Attention_Ads
 */

namespace Attention_Ads\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ad_Calculator_Widget – Elementor widget that renders the multi-ad-type
 * pricing calculator with live JS updates.
 */
class Ad_Calculator_Widget extends Widget_Base {

	// -------------------------------------------------------------------------
	// Widget identity
	// -------------------------------------------------------------------------

	/** @inheritdoc */
	public function get_name() {
		return 'attention_ads_calculator';
	}

	/** @inheritdoc */
	public function get_title() {
		return esc_html__( 'Ad Package Calculator', 'attention-ads-calculator' );
	}

	/** @inheritdoc */
	public function get_icon() {
		return 'eicon-price-table';
	}

	/** @inheritdoc */
	public function get_categories() {
		return [ 'general' ];
	}

	/** @inheritdoc */
	public function get_keywords() {
		return [ 'calculator', 'pricing', 'ads', 'package', 'attention' ];
	}

	/** @inheritdoc */
	public function get_style_depends() {
		return [ 'attention-ads-calculator' ];
	}

	/** @inheritdoc */
	public function get_script_depends() {
		return [ 'attention-ads-calculator' ];
	}

	// -------------------------------------------------------------------------
	// Controls
	// -------------------------------------------------------------------------

	/** @inheritdoc */
	protected function register_controls() {

		// ── Section: General Settings ──────────────────────────────────────────
		$this->start_controls_section(
			'section_general',
			[
				'label' => esc_html__( 'General Settings', 'attention-ads-calculator' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'calculator_title',
			[
				'label'       => esc_html__( 'Calculator Title', 'attention-ads-calculator' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Build Your Ad Package', 'attention-ads-calculator' ),
				'placeholder' => esc_html__( 'Enter title…', 'attention-ads-calculator' ),
			]
		);

		$this->add_control(
			'calculator_subtitle',
			[
				'label'   => esc_html__( 'Subtitle / Description', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Mix and match ad types. Credits never expire and can be used flexibly.', 'attention-ads-calculator' ),
				'rows'    => 3,
			]
		);

		$this->add_control(
			'default_currency',
			[
				'label'   => esc_html__( 'Default Currency', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'GBP' => '£ GBP',
					'USD' => '$ USD',
					'EUR' => '€ EUR',
					'AED' => 'AED',
					'AUD' => 'A$ AUD',
				],
				'default' => 'GBP',
			]
		);

		$this->add_control(
			'show_currency_switcher',
			[
				'label'        => esc_html__( 'Show Currency Switcher', 'attention-ads-calculator' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'attention-ads-calculator' ),
				'label_off'    => esc_html__( 'No', 'attention-ads-calculator' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'revision_price_gbp',
			[
				'label'   => esc_html__( 'Revision Price (GBP per asset)', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0,
				'step'    => 1,
				'default' => 30,
			]
		);

		$this->add_control(
			'credits_message',
			[
				'label'   => esc_html__( '"Credits" Footer Message', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '✓ Credits never expire and can be used flexibly across any ad type.', 'attention-ads-calculator' ),
			]
		);

		$this->end_controls_section();

		// ── Section: Bulk Discount Tiers ──────────────────────────────────────
		$this->start_controls_section(
			'section_discounts',
			[
				'label' => esc_html__( 'Bulk Discount Tiers', 'attention-ads-calculator' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'discount_tier1_max',
			[
				'label'   => esc_html__( 'Tier 1 – Max Quantity', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 5,
				'min'     => 1,
			]
		);

		$this->add_control(
			'discount_tier1_pct',
			[
				'label'   => esc_html__( 'Tier 1 – Discount %', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,
				'min'     => 0,
				'max'     => 100,
			]
		);

		$this->add_control(
			'discount_tier2_max',
			[
				'label'   => esc_html__( 'Tier 2 – Max Quantity', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 15,
				'min'     => 1,
			]
		);

		$this->add_control(
			'discount_tier2_pct',
			[
				'label'   => esc_html__( 'Tier 2 – Discount %', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 10,
				'min'     => 0,
				'max'     => 100,
			]
		);

		$this->add_control(
			'discount_tier3_max',
			[
				'label'   => esc_html__( 'Tier 3 – Max Quantity', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 30,
				'min'     => 1,
			]
		);

		$this->add_control(
			'discount_tier3_pct',
			[
				'label'   => esc_html__( 'Tier 3 – Discount %', 'attention-ads-calculator' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 20,
				'min'     => 0,
				'max'     => 100,
			]
		);

		$this->add_control(
			'discount_tier4_pct',
			[
				'label'       => esc_html__( 'Tier 4 – Discount % (30+ units)', 'attention-ads-calculator' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 30,
				'min'         => 0,
				'max'         => 100,
				'description' => esc_html__( 'Applied when quantity exceeds Tier 3 max.', 'attention-ads-calculator' ),
			]
		);

		$this->end_controls_section();

		// ── Section: Style Controls ────────────────────────────────────────────
		$this->start_controls_section(
			'section_style_general',
			[
				'label' => esc_html__( 'Colours & Typography', 'attention-ads-calculator' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label'     => esc_html__( 'Accent Colour', 'attention-ads-calculator' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6c63ff',
				'selectors' => [
					'{{WRAPPER}} .aac-btn-primary'                => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .aac-slider-range'               => 'accent-color: {{VALUE}};',
					'{{WRAPPER}} .aac-total-value'                => 'color: {{VALUE}};',
					'{{WRAPPER}} .aac-discount-badge'             => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .aac-ad-block-header'            => 'border-left-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label'     => esc_html__( 'Card Background', 'attention-ads-calculator' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .aac-ad-block'   => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .aac-summary'     => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wrapper_bg_color',
			[
				'label'     => esc_html__( 'Wrapper Background', 'attention-ads-calculator' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f7f7fb',
				'selectors' => [
					'{{WRAPPER}} .aac-calculator' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Title Typography', 'attention-ads-calculator' ),
				'selector' => '{{WRAPPER}} .aac-title',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'body_typography',
				'label'    => esc_html__( 'Body Typography', 'attention-ads-calculator' ),
				'selector' => '{{WRAPPER}} .aac-calculator',
			]
		);

		$this->end_controls_section();
	}

	// -------------------------------------------------------------------------
	// Render
	// -------------------------------------------------------------------------

	/** @inheritdoc */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Build the pricing config to pass to JS.
		$config = $this->build_js_config( $settings );

		$widget_id = $this->get_id();
		?>
		<div class="aac-calculator"
			 id="aac-<?php echo esc_attr( $widget_id ); ?>"
			 data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">

			<?php $this->render_header( $settings ); ?>
			<?php $this->render_currency_switcher( $settings, $config ); ?>
			<?php $this->render_ad_type_selector(); ?>

			<!-- Dynamic ad blocks injected here by JS -->
			<div class="aac-blocks-container" aria-live="polite"></div>

			<?php $this->render_add_button(); ?>
			<?php $this->render_summary( $settings ); ?>
			<?php $this->render_footer( $settings ); ?>

		</div><!-- /.aac-calculator -->
		<?php
	}

	// -------------------------------------------------------------------------
	// Render helpers
	// -------------------------------------------------------------------------

	/**
	 * Render the calculator header (title + subtitle).
	 */
	private function render_header( array $settings ) {
		if ( ! empty( $settings['calculator_title'] ) ) {
			printf(
				'<h2 class="aac-title">%s</h2>',
				esc_html( $settings['calculator_title'] )
			);
		}
		if ( ! empty( $settings['calculator_subtitle'] ) ) {
			printf(
				'<p class="aac-subtitle">%s</p>',
				esc_html( $settings['calculator_subtitle'] )
			);
		}
	}

	/**
	 * Render the currency switcher dropdown.
	 */
	private function render_currency_switcher( array $settings, array $config ) {
		if ( 'yes' !== $settings['show_currency_switcher'] ) {
			return;
		}

		$currencies = $config['currencies'];
		$default    = $settings['default_currency'];
		?>
		<div class="aac-currency-row">
			<label class="aac-currency-label" for="aac-currency-select">
				<?php esc_html_e( 'Currency:', 'attention-ads-calculator' ); ?>
			</label>
			<select class="aac-currency-select" id="aac-currency-select" aria-label="<?php esc_attr_e( 'Select currency', 'attention-ads-calculator' ); ?>">
				<?php foreach ( $currencies as $code => $info ) : ?>
					<option value="<?php echo esc_attr( $code ); ?>"
							<?php selected( $code, $default ); ?>>
						<?php echo esc_html( $info['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Render the "Add Ad Type" button and selector dropdown.
	 */
	private function render_ad_type_selector() {
		?>
		<div class="aac-add-row">
			<label class="aac-add-label" for="aac-ad-type-select">
				<?php esc_html_e( 'Select Ad Type', 'attention-ads-calculator' ); ?>
			</label>
			<select class="aac-ad-type-select" id="aac-ad-type-select" aria-label="<?php esc_attr_e( 'Ad type to add', 'attention-ads-calculator' ); ?>">
				<option value="">— <?php esc_html_e( 'Choose an ad type', 'attention-ads-calculator' ); ?> —</option>
				<!-- Options populated by JS from config.adTypes -->
			</select>
		</div>
		<?php
	}

	/**
	 * Render the "Add to Package" button.
	 */
	private function render_add_button() {
		?>
		<div class="aac-add-btn-row">
			<button class="aac-btn-primary aac-btn-add" type="button" aria-label="<?php esc_attr_e( 'Add selected ad type to package', 'attention-ads-calculator' ); ?>">
				+ <?php esc_html_e( 'Add to Package', 'attention-ads-calculator' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Render the package summary / breakdown section.
	 */
	private function render_summary( array $settings ) {
		?>
		<div class="aac-summary" aria-label="<?php esc_attr_e( 'Package summary', 'attention-ads-calculator' ); ?>">
			<h3 class="aac-summary-title"><?php esc_html_e( 'Your Package', 'attention-ads-calculator' ); ?></h3>

			<!-- Itemised list -->
			<ul class="aac-breakdown-list" aria-live="polite">
				<li class="aac-breakdown-empty"><?php esc_html_e( 'No ad types added yet.', 'attention-ads-calculator' ); ?></li>
			</ul>

			<hr class="aac-divider">

			<div class="aac-total-row">
				<span class="aac-total-label"><?php esc_html_e( 'Total Package Price', 'attention-ads-calculator' ); ?></span>
				<span class="aac-total-value" aria-live="polite">—</span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the footer credits message.
	 */
	private function render_footer( array $settings ) {
		if ( ! empty( $settings['credits_message'] ) ) {
			printf(
				'<p class="aac-credits-message">%s</p>',
				esc_html( $settings['credits_message'] )
			);
		}
	}

	// -------------------------------------------------------------------------
	// JS config builder
	// -------------------------------------------------------------------------

	/**
	 * Build the configuration array that is serialised into the widget's
	 * data-config attribute and consumed by the front-end JavaScript.
	 */
	private function build_js_config( array $settings ): array {
		return [
			'defaultCurrency' => $settings['default_currency'],
			'revisionPrice'   => (float) $settings['revision_price_gbp'],
			'discountTiers'   => [
				[
					'max'     => (int) $settings['discount_tier1_max'],
					'percent' => (float) $settings['discount_tier1_pct'],
				],
				[
					'max'     => (int) $settings['discount_tier2_max'],
					'percent' => (float) $settings['discount_tier2_pct'],
				],
				[
					'max'     => (int) $settings['discount_tier3_max'],
					'percent' => (float) $settings['discount_tier3_pct'],
				],
				[
					'max'     => PHP_INT_MAX,
					'percent' => (float) $settings['discount_tier4_pct'],
				],
			],
			'currencies'      => $this->get_currency_map(),
			'adTypes'         => $this->get_ad_types(),
		];
	}

	/**
	 * Currency map: rate is relative to GBP.
	 */
	private function get_currency_map(): array {
		return [
			'GBP' => [ 'label' => '£ GBP', 'symbol' => '£',   'rate' => 1.00 ],
			'USD' => [ 'label' => '$ USD', 'symbol' => '$',   'rate' => 1.27 ],
			'EUR' => [ 'label' => '€ EUR', 'symbol' => '€',   'rate' => 1.17 ],
			'AED' => [ 'label' => 'AED',   'symbol' => 'AED ', 'rate' => 4.67 ],
			'AUD' => [ 'label' => 'A$ AUD','symbol' => 'A$',  'rate' => 1.96 ],
		];
	}

	/**
	 * Ad types definition (base prices in GBP, style modifiers).
	 */
	private function get_ad_types(): array {
		return [
			[
				'id'       => 'ugc_video',
				'label'    => 'UGC Video',
				'unit'     => 'per video',
				'basePrice'=> 200,
				'styles'   => [
					[ 'id' => 'standard',    'label' => 'Standard UGC',                             'multiplier' => 1.00 ],
					[ 'id' => 'creator_led', 'label' => 'Creator-led / Scripted',                   'multiplier' => 1.10 ],
					[ 'id' => 'performance', 'label' => 'Performance-optimised / Hooks + Variations','multiplier' => 1.25 ],
				],
			],
			[
				'id'       => 'motion_ad',
				'label'    => 'Motion Ad',
				'unit'     => 'per asset',
				'basePrice'=> 400,
				'styles'   => [
					[ 'id' => 'basic',    'label' => 'Basic Animation',              'multiplier' => 1.00 ],
					[ 'id' => 'advanced', 'label' => 'Advanced Motion / Transitions','multiplier' => 1.20 ],
				],
			],
			[
				'id'       => 'graphic_ad',
				'label'    => 'Graphic Ad',
				'unit'     => 'per asset',
				'basePrice'=> 150,
				'styles'   => [
					[ 'id' => 'standard', 'label' => 'Standard',          'multiplier' => 1.00 ],
					[ 'id' => 'premium',  'label' => 'Premium / Bespoke', 'multiplier' => 1.20 ],
				],
			],
			[
				'id'       => 'video_edit',
				'label'    => 'Video Edit',
				'unit'     => 'per edit',
				'basePrice'=> 180,
				'styles'   => [
					[ 'id' => 'standard',    'label' => 'Standard Edit',                   'multiplier' => 1.00 ],
					[ 'id' => 'performance', 'label' => 'Performance-optimised / Reformat', 'multiplier' => 1.15 ],
				],
			],
			[
				'id'       => 'creative_brief',
				'label'    => 'Creative Brief',
				'unit'     => 'per brief',
				'basePrice'=> 120,
				'styles'   => [
					[ 'id' => 'standard', 'label' => 'Standard Brief',  'multiplier' => 1.00 ],
					[ 'id' => 'detailed', 'label' => 'Detailed / Full', 'multiplier' => 1.25 ],
				],
			],
			[
				'id'       => 'out_of_home',
				'label'    => 'Out of Home',
				'unit'     => 'per asset',
				'basePrice'=> 350,
				'styles'   => [
					[ 'id' => 'standard', 'label' => 'Standard',        'multiplier' => 1.00 ],
					[ 'id' => 'premium',  'label' => 'Premium / Large', 'multiplier' => 1.20 ],
				],
			],
			[
				'id'       => 'audio_ad',
				'label'    => 'Audio for Ads',
				'unit'     => 'per asset',
				'basePrice'=> 1000,
				'styles'   => [
					[ 'id' => 'standard', 'label' => 'Standard',                    'multiplier' => 1.00 ],
					[ 'id' => 'premium',  'label' => 'Bespoke / Full Production',   'multiplier' => 1.25 ],
				],
			],
		];
	}
}
