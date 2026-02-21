<?php
/**
 * Plugin Name: Attention Ads Package Calculator
 * Description: Elementor widget for building ad packages with dynamic pricing logic.
 * Version: 1.0.0
 * Author: AttentionAds
 */

if (!defined('ABSPATH')) {
    exit;
}

final class AttentionAds_Calculator_Plugin {
    const VERSION = '1.0.0';

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        if (!did_action('elementor/loaded')) {
            return;
        }

        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    }

    public function register_assets() {
        wp_register_style(
            'attentionads-calculator',
            plugin_dir_url(__FILE__) . 'assets/css/calculator.css',
            [],
            self::VERSION
        );

        wp_register_script(
            'attentionads-calculator',
            plugin_dir_url(__FILE__) . 'assets/js/calculator.js',
            [],
            self::VERSION,
            true
        );
    }

    public function register_widgets($widgets_manager) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-attentionads-calculator-widget.php';
        $widgets_manager->register(new \AttentionAds_Calculator_Widget());
    }
}

new AttentionAds_Calculator_Plugin();
