<?php
/**
 * Plugin Name:       Attention Ads – Package Calculator
 * Plugin URI:        https://github.com/Asfand6417/Build-Your-Ad-Package-Calculator
 * Description:       A dynamic, multi-ad-type pricing calculator built as an Elementor widget. Supports bulk discounts, style multipliers, revisions, and multi-currency output.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Attention Ads
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       attention-ads-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'AAC_VERSION', '1.0.0' );
define( 'AAC_FILE', __FILE__ );
define( 'AAC_PATH', plugin_dir_path( __FILE__ ) );
define( 'AAC_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check Elementor is active before loading the widget.
 */
function aac_check_elementor() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'aac_missing_elementor_notice' );
		return;
	}

	// Elementor minimum version check.
	$elementor_version_required = '3.0.0';
	if ( ! version_compare( ELEMENTOR_VERSION, $elementor_version_required, '>=' ) ) {
		add_action( 'admin_notices', 'aac_outdated_elementor_notice' );
		return;
	}

	// Load the widget.
	require_once AAC_PATH . 'widgets/class-ad-calculator-widget.php';

	add_action( 'elementor/widgets/register', 'aac_register_widget' );
	add_action( 'elementor/frontend/after_enqueue_styles', 'aac_enqueue_styles' );
	add_action( 'elementor/frontend/after_register_scripts', 'aac_register_scripts' );
}
add_action( 'plugins_loaded', 'aac_check_elementor' );

/**
 * Register the Elementor widget.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
 */
function aac_register_widget( $widgets_manager ) {
	$widgets_manager->register( new \Attention_Ads\Widgets\Ad_Calculator_Widget() );
}

/**
 * Enqueue front-end styles.
 */
function aac_enqueue_styles() {
	wp_enqueue_style(
		'attention-ads-calculator',
		AAC_URL . 'assets/css/calculator.css',
		[],
		AAC_VERSION
	);
}

/**
 * Register front-end scripts (enqueued on demand by the widget).
 */
function aac_register_scripts() {
	wp_register_script(
		'attention-ads-calculator',
		AAC_URL . 'assets/js/calculator.js',
		[],
		AAC_VERSION,
		true
	);
}

/**
 * Admin notice: Elementor not installed/activated.
 */
function aac_missing_elementor_notice() {
	$message = sprintf(
		/* translators: 1: Plugin name, 2: Elementor */
		esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'attention-ads-calculator' ),
		'<strong>' . esc_html__( 'Attention Ads Calculator', 'attention-ads-calculator' ) . '</strong>',
		'<strong>' . esc_html__( 'Elementor', 'attention-ads-calculator' ) . '</strong>'
	);
	printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
}

/**
 * Admin notice: Elementor version too old.
 */
function aac_outdated_elementor_notice() {
	$message = sprintf(
		/* translators: 1: Plugin name, 2: Elementor, 3: Required version */
		esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'attention-ads-calculator' ),
		'<strong>' . esc_html__( 'Attention Ads Calculator', 'attention-ads-calculator' ) . '</strong>',
		'<strong>' . esc_html__( 'Elementor', 'attention-ads-calculator' ) . '</strong>',
		'3.0.0'
	);
	printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
}
