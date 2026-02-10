<?php
/**
 * Widget Visibility with Descendants
 *
 * @package           WidgetVisibilityDescendants
 * @author            Ercan ATAY
 * @copyright         2025 Ercan ATAY
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Widget Visibility with Descendants
 * Plugin URI:        https://github.com/ercanatay/widget-visibility-descendants
 * Description:       Control widget visibility based on pages, posts, categories with full descendant (grandchildren) support. A Jetpack-free alternative that includes ALL levels of nested pages.
 * Version:           1.4.1
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Ercan ATAY
 * Author URI:        https://www.ercanatay.com/en/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       widget-visibility-descendants-main
 * Domain Path:       /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('WVD_VERSION', '1.4.1');
define('WVD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WVD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WVD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 *
 * @since 1.0.0
 */
final class Widget_Visibility_Descendants {

    /**
     * Single instance of the class
     *
     * @var Widget_Visibility_Descendants
     */
    private static $instance = null;

    /**
     * Get single instance of the class
     *
     * @since 1.0.0
     * @return Widget_Visibility_Descendants
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     *
     * @since 1.0.0
     */
    private function load_dependencies() {
        require_once WVD_PLUGIN_DIR . 'includes/class-visibility-admin.php';
        require_once WVD_PLUGIN_DIR . 'includes/class-visibility-frontend.php';
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Load translations
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Initialize data handling hooks (must also run for REST widget updates).
        new WVD_Visibility_Admin();

        // Initialize frontend
        new WVD_Visibility_Frontend();
    }

    /**
     * Load plugin textdomain for translations
     *
     * @since 1.0.0
     */
    public function load_textdomain() {
        // Translations are automatically loaded by WordPress 4.6+ for plugins hosted on WordPress.org
    }
}

/**
 * Initialize plugin
 *
 * @since 1.0.0
 * @return Widget_Visibility_Descendants
 */
function wvd_init() {
    return Widget_Visibility_Descendants::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'wvd_init');

/**
 * Activation hook
 *
 * @since 1.0.0
 */
register_activation_hook(__FILE__, function() {
    // Flush rewrite rules if needed
    flush_rewrite_rules();
});

/**
 * Deactivation hook
 *
 * @since 1.0.0
 */
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
