<?php
/**
 * Admin menu page for Cybokron Advanced Widget Visibility
 *
 * @package CybokronAdvancedWidgetVisibility
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Page Class
 *
 * @since 1.6.1
 */
class WVD_Admin_Page {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    /**
     * Register admin menu page
     */
    public function register_menu() {
        $icon_path = WVD_PLUGIN_DIR . 'assets/images/icon-20x20.png';
        $icon_url = 'dashicons-visibility';

        if (file_exists($icon_path)) {
            $icon_data = file_get_contents($icon_path);
            if ($icon_data !== false) {
                $icon_url = 'data:image/png;base64,' . base64_encode($icon_data);
            }
        }

        add_menu_page(
            __('Widget Visibility', 'widget-visibility-with-descendants'),
            __('Widget Visibility', 'widget-visibility-with-descendants'),
            'edit_theme_options',
            'wvd-about',
            [$this, 'render_page'],
            $icon_url,
            59
        );
    }

    /**
     * Render the about page
     */
    public function render_page() {
        $icon_url = WVD_PLUGIN_URL . 'assets/images/icon-128x128.png';
        ?>
        <div class="wrap wvd-about-wrap">
            <h1>
                <?php if (file_exists(WVD_PLUGIN_DIR . 'assets/images/icon-128x128.png')): ?>
                    <img src="<?php echo esc_url($icon_url); ?>" alt="" width="36" height="36" style="vertical-align: middle; margin-right: 10px;">
                <?php endif; ?>
                <?php esc_html_e('Cybokron Advanced Widget Visibility', 'widget-visibility-with-descendants'); ?>
            </h1>

            <p class="about-text">
                <?php
                printf(
                    /* translators: %s: plugin version */
                    esc_html__('Version %s', 'widget-visibility-with-descendants'),
                    esc_html(WVD_VERSION)
                );
                ?>
            </p>

            <div class="wvd-about-section">
                <h2><?php esc_html_e('Getting Started', 'widget-visibility-with-descendants'); ?></h2>
                <ol>
                    <li>
                        <?php
                        printf(
                            /* translators: %s: link to widgets page */
                            esc_html__('Go to %s', 'widget-visibility-with-descendants'),
                            '<a href="' . esc_url(admin_url('widgets.php')) . '">' . esc_html__('Appearance &rarr; Widgets', 'widget-visibility-with-descendants') . '</a>'
                        );
                        ?>
                    </li>
                    <li><?php esc_html_e('Edit any widget and click the "Visibility" button', 'widget-visibility-with-descendants'); ?></li>
                    <li><?php esc_html_e('Choose Show or Hide, select conditions, and save', 'widget-visibility-with-descendants'); ?></li>
                </ol>
            </div>

            <div class="wvd-about-section">
                <h2><?php esc_html_e('Features', 'widget-visibility-with-descendants'); ?></h2>
                <ul class="wvd-feature-list">
                    <li><?php esc_html_e('Page visibility with full descendant support', 'widget-visibility-with-descendants'); ?></li>
                    <li><?php esc_html_e('Category visibility with hierarchy support', 'widget-visibility-with-descendants'); ?></li>
                    <li><?php esc_html_e('Hierarchical custom taxonomy visibility', 'widget-visibility-with-descendants'); ?></li>
                    <li><?php esc_html_e('Post type, special pages, and user state conditions', 'widget-visibility-with-descendants'); ?></li>
                    <li><?php esc_html_e('Multiple conditions with AND/OR logic', 'widget-visibility-with-descendants'); ?></li>
                    <li><?php esc_html_e('User role targeting', 'widget-visibility-with-descendants'); ?></li>
                </ul>
            </div>

            <div class="wvd-about-section">
                <p>
                    <a href="<?php echo esc_url(admin_url('widgets.php')); ?>" class="button button-primary">
                        <?php esc_html_e('Go to Widgets', 'widget-visibility-with-descendants'); ?>
                    </a>
                </p>
            </div>
        </div>

        <style>
            .wvd-about-wrap { max-width: 800px; }
            .wvd-about-wrap .about-text { font-size: 1.2em; color: #666; margin: 0.5em 0 1.5em; }
            .wvd-about-section { margin-bottom: 2em; }
            .wvd-about-section h2 { font-size: 1.3em; border-bottom: 1px solid #ddd; padding-bottom: 0.5em; }
            .wvd-feature-list { list-style: disc; padding-left: 20px; }
            .wvd-feature-list li { margin-bottom: 0.5em; }
        </style>
        <?php
    }
}
