<?php
/**
 * Updater settings for Widget Visibility with Descendants.
 *
 * @package Widget_Visibility_Descendants
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Updater settings class.
 */
class WVD_Updater_Settings {

    /**
     * Option name used to persist updater preferences.
     */
    const OPTION_NAME = 'wvd_updater_settings';

    /**
     * Settings group name.
     */
    const SETTINGS_GROUP = 'wvd_updater_settings_group';

    /**
     * Settings page slug.
     */
    const PAGE_SLUG = 'wvd-updates';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_filter('plugin_action_links_' . WVD_PLUGIN_BASENAME, [$this, 'add_plugin_action_links']);
    }

    /**
     * Default updater settings.
     */
    public static function get_defaults() {
        return [
            'enabled' => true,
            'channel' => 'stable',
        ];
    }

    /**
     * Get sanitized updater settings.
     */
    public static function get_settings() {
        $settings = get_option(self::OPTION_NAME, []);
        if (!is_array($settings)) {
            $settings = [];
        }

        return wp_parse_args(self::sanitize_settings($settings), self::get_defaults());
    }

    /**
     * Check if updater integration is enabled.
     */
    public static function is_enabled() {
        $settings = self::get_settings();
        return !empty($settings['enabled']);
    }

    /**
     * Get selected update channel.
     */
    public static function get_channel() {
        $settings = self::get_settings();
        return in_array($settings['channel'], ['stable', 'beta'], true) ? $settings['channel'] : 'stable';
    }

    /**
     * Register updater settings.
     */
    public function register_settings() {
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_NAME,
            [$this, 'sanitize_and_save_settings']
        );
    }

    /**
     * Register settings page under Settings menu.
     */
    public function register_settings_page() {
        add_options_page(
            __('Widget Visibility Updates', 'widget-visibility-descendants-main'),
            __('Widget Visibility Updates', 'widget-visibility-descendants-main'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_settings_page']
        );
    }

    /**
     * Add quick link on Plugins page.
     */
    public function add_plugin_action_links($links) {
        if (!is_array($links)) {
            $links = [];
        }

        $settings_url = admin_url('options-general.php?page=' . self::PAGE_SLUG);
        $settings_link = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url($settings_url),
            esc_html__('Update Settings', 'widget-visibility-descendants-main')
        );

        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Sanitize option payload before save.
     */
    public function sanitize_and_save_settings($settings) {
        $sanitized = self::sanitize_settings($settings);
        self::clear_update_caches();
        return $sanitized;
    }

    /**
     * Sanitize updater option values.
     */
    public static function sanitize_settings($settings) {
        $defaults = self::get_defaults();

        if (!is_array($settings)) {
            return $defaults;
        }

        $enabled = !empty($settings['enabled']);

        $channel = isset($settings['channel']) && is_scalar($settings['channel'])
            ? sanitize_key((string) $settings['channel'])
            : $defaults['channel'];

        if (!in_array($channel, ['stable', 'beta'], true)) {
            $channel = 'stable';
        }

        return [
            'enabled' => $enabled,
            'channel' => $channel,
        ];
    }

    /**
     * Clear update transients after settings changes.
     */
    public static function clear_update_caches() {
        delete_site_transient('update_plugins');
        delete_transient('update_plugins');

        if (class_exists('WVD_GitHub_Updater')) {
            WVD_GitHub_Updater::clear_updater_cache();
        } else {
            delete_site_transient('wvd_github_releases_cache');
        }
    }

    /**
     * Render updater settings page.
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = self::get_settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Widget Visibility Updates', 'widget-visibility-descendants-main'); ?></h1>
            <p>
                <?php
                printf(
                    esc_html__('Installed version: %s', 'widget-visibility-descendants-main'),
                    esc_html(WVD_VERSION)
                );
                ?>
            </p>

            <form method="post" action="options.php">
                <?php settings_fields(self::SETTINGS_GROUP); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable GitHub updates', 'widget-visibility-descendants-main'); ?></th>
                        <td>
                            <label for="wvd-updater-enabled">
                                <input type="hidden" name="<?php echo esc_attr(self::OPTION_NAME); ?>[enabled]" value="0">
                                <input
                                    id="wvd-updater-enabled"
                                    type="checkbox"
                                    name="<?php echo esc_attr(self::OPTION_NAME); ?>[enabled]"
                                    value="1"
                                    <?php checked(!empty($settings['enabled'])); ?>
                                >
                                <?php esc_html_e('Check GitHub releases and deliver updates through WordPress updater.', 'widget-visibility-descendants-main'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Update channel', 'widget-visibility-descendants-main'); ?></th>
                        <td>
                            <select name="<?php echo esc_attr(self::OPTION_NAME); ?>[channel]">
                                <option value="stable" <?php selected($settings['channel'], 'stable'); ?>>
                                    <?php esc_html_e('Stable (recommended)', 'widget-visibility-descendants-main'); ?>
                                </option>
                                <option value="beta" <?php selected($settings['channel'], 'beta'); ?>>
                                    <?php esc_html_e('Beta (includes prereleases)', 'widget-visibility-descendants-main'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Choose which GitHub releases are eligible for updates.', 'widget-visibility-descendants-main'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
