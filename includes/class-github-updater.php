<?php
/**
 * GitHub updater integration for Widget Visibility with Descendants.
 *
 * @package Widget_Visibility_Descendants
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GitHub updater class.
 */
class WVD_GitHub_Updater {

    /**
     * Cached GitHub releases transient key.
     */
    const RELEASE_CACHE_KEY = 'wvd_github_releases_cache';

    /**
     * Release cache TTL in seconds.
     */
    const RELEASE_CACHE_TTL = HOUR_IN_SECONDS;

    /**
     * GitHub releases API endpoint.
     */
    const RELEASES_API_URL = 'https://api.github.com/repos/ercanatay/widget-visibility-descendants/releases?per_page=10';

    /**
     * Base repository URL.
     *
     * @var string
     */
    private $repository_url = 'https://github.com/ercanatay/widget-visibility-descendants';

    /**
     * Plugin slug.
     *
     * @var string
     */
    private $plugin_slug = 'widget-visibility-descendants';

    /**
     * Plugin file basename.
     *
     * @var string
     */
    private $plugin_basename = WVD_PLUGIN_BASENAME;

    /**
     * Constructor.
     */
    public function __construct() {
        $detected_slug = dirname($this->plugin_basename);
        if (is_string($detected_slug) && '' !== $detected_slug && '.' !== $detected_slug) {
            $this->plugin_slug = sanitize_key($detected_slug);
        }

        add_filter('pre_set_site_transient_update_plugins', [$this, 'inject_update_transient']);
        add_filter('plugins_api', [$this, 'provide_plugin_information'], 10, 3);
        add_filter('upgrader_source_selection', [$this, 'normalize_package_source'], 10, 4);
        add_action('upgrader_process_complete', [$this, 'clear_cache_after_upgrade'], 10, 2);
    }

    /**
     * Clear updater-specific cache.
     */
    public static function clear_updater_cache() {
        delete_site_transient(self::RELEASE_CACHE_KEY);
    }

    /**
     * Inject GitHub updates into WordPress plugin updates transient.
     */
    public function inject_update_transient($transient) {
        if (!is_object($transient)) {
            $transient = new stdClass();
        }

        if (!isset($transient->response) || !is_array($transient->response)) {
            $transient->response = [];
        }

        if (!isset($transient->no_update) || !is_array($transient->no_update)) {
            $transient->no_update = [];
        }

        if (!$this->is_updater_enabled()) {
            unset($transient->response[$this->plugin_basename], $transient->no_update[$this->plugin_basename]);
            return $transient;
        }

        if (empty($transient->checked) || !is_array($transient->checked) || !isset($transient->checked[$this->plugin_basename])) {
            return $transient;
        }

        $latest_release = $this->get_latest_release($this->get_selected_channel());
        if (empty($latest_release)) {
            unset($transient->response[$this->plugin_basename]);
            return $transient;
        }

        $current_version = $transient->checked[$this->plugin_basename];
        if (!is_scalar($current_version) || '' === (string) $current_version) {
            $current_version = WVD_VERSION;
        } else {
            $current_version = sanitize_text_field((string) $current_version);
        }

        if (version_compare($latest_release['version'], $current_version, '>')) {
            $transient->response[$this->plugin_basename] = (object) [
                'id' => $this->repository_url,
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_basename,
                'new_version' => $latest_release['version'],
                'url' => esc_url_raw($latest_release['html_url']),
                'package' => esc_url_raw($this->build_package_url($latest_release['tag'])),
                'requires' => '5.2',
                'requires_php' => '7.4',
                'tested' => '',
            ];

            unset($transient->no_update[$this->plugin_basename]);
            return $transient;
        }

        unset($transient->response[$this->plugin_basename]);
        $transient->no_update[$this->plugin_basename] = (object) [
            'id' => $this->repository_url,
            'slug' => $this->plugin_slug,
            'plugin' => $this->plugin_basename,
            'new_version' => $current_version,
            'url' => esc_url_raw($this->repository_url),
            'package' => '',
            'requires' => '5.2',
            'requires_php' => '7.4',
            'tested' => '',
        ];

        return $transient;
    }

    /**
     * Provide data for "View details" modal.
     */
    public function provide_plugin_information($result, $action, $args) {
        if ('plugin_information' !== $action || !is_object($args) || empty($args->slug) || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        $latest_release = $this->get_latest_release($this->get_selected_channel());
        if (empty($latest_release)) {
            return $result;
        }

        $changelog = trim((string) $latest_release['body']);
        if ('' === $changelog) {
            $changelog = esc_html__('No release notes available.', 'widget-visibility-descendants-main');
        } else {
            $changelog = wpautop(esc_html($changelog));
        }

        return (object) [
            'name' => 'Widget Visibility with Descendants',
            'slug' => $this->plugin_slug,
            'plugin' => $this->plugin_basename,
            'version' => $latest_release['version'],
            'author' => '<a href="https://www.ercanatay.com/en/">Ercan ATAY</a>',
            'homepage' => esc_url_raw($this->repository_url),
            'download_link' => esc_url_raw($this->build_package_url($latest_release['tag'])),
            'requires' => '5.2',
            'requires_php' => '7.4',
            'last_updated' => $latest_release['published_at'],
            'sections' => [
                'description' => wpautop(esc_html__('Control widget visibility based on pages, posts, categories with full descendant support.', 'widget-visibility-descendants-main')),
                'changelog' => $changelog,
            ],
        ];
    }

    /**
     * Rename extracted package directory to plugin directory name.
     */
    public function normalize_package_source($source, $remote_source, $upgrader, $hook_extra) {
        if (!is_array($hook_extra)) {
            return $source;
        }

        $updated_plugins = [];

        if (isset($hook_extra['plugins']) && is_array($hook_extra['plugins'])) {
            $updated_plugins = $hook_extra['plugins'];
        }

        if (isset($hook_extra['plugin']) && is_scalar($hook_extra['plugin'])) {
            $updated_plugins[] = (string) $hook_extra['plugin'];
        }

        if (!in_array($this->plugin_basename, $updated_plugins, true)) {
            return $source;
        }

        if (!is_string($source) || !is_string($remote_source) || !is_dir($source)) {
            return $source;
        }

        $target_source = trailingslashit($remote_source) . $this->plugin_slug;
        if (untrailingslashit($source) === untrailingslashit($target_source)) {
            return $source;
        }

        global $wp_filesystem;
        if (!is_object($wp_filesystem)) {
            return $source;
        }

        if ($wp_filesystem->exists($target_source)) {
            $wp_filesystem->delete($target_source, true);
        }

        $moved = $wp_filesystem->move($source, $target_source, true);
        if (!$moved) {
            return new WP_Error(
                'wvd_updater_source_rename_failed',
                __('Could not prepare the GitHub update package for installation.', 'widget-visibility-descendants-main')
            );
        }

        return $target_source;
    }

    /**
     * Clear update cache after this plugin gets upgraded.
     */
    public function clear_cache_after_upgrade($upgrader, $hook_extra) {
        if (!is_array($hook_extra) || empty($hook_extra['type']) || 'plugin' !== $hook_extra['type']) {
            return;
        }

        $updated_plugins = [];

        if (isset($hook_extra['plugins']) && is_array($hook_extra['plugins'])) {
            $updated_plugins = $hook_extra['plugins'];
        }

        if (isset($hook_extra['plugin']) && is_scalar($hook_extra['plugin'])) {
            $updated_plugins[] = (string) $hook_extra['plugin'];
        }

        if (!in_array($this->plugin_basename, $updated_plugins, true)) {
            return;
        }

        self::clear_updater_cache();
        delete_site_transient('update_plugins');
        delete_transient('update_plugins');
    }

    /**
     * Get the latest eligible GitHub release for selected channel.
     */
    private function get_latest_release($channel) {
        $releases = $this->get_releases();
        if (empty($releases)) {
            return null;
        }

        $latest = null;
        foreach ($releases as $release) {
            $candidate = $this->normalize_release_payload($release, $channel);
            if (null === $candidate) {
                continue;
            }

            if (null === $latest || version_compare($candidate['version'], $latest['version'], '>')) {
                $latest = $candidate;
            }
        }

        return $latest;
    }

    /**
     * Return normalized release payload or null when invalid/ineligible.
     */
    private function normalize_release_payload($release, $channel) {
        if (!is_array($release)) {
            return null;
        }

        if (!empty($release['draft'])) {
            return null;
        }

        $is_prerelease = !empty($release['prerelease']);
        if ('stable' === $channel && $is_prerelease) {
            return null;
        }

        if (empty($release['tag_name']) || !is_scalar($release['tag_name'])) {
            return null;
        }

        $tag = sanitize_text_field((string) $release['tag_name']);
        $version = $this->normalize_tag_to_version($tag);
        if (null === $version) {
            return null;
        }

        $html_url = isset($release['html_url']) && is_scalar($release['html_url'])
            ? esc_url_raw((string) $release['html_url'])
            : '';

        if ('' === $html_url) {
            $html_url = $this->repository_url;
        }

        $body = isset($release['body']) && is_scalar($release['body'])
            ? (string) $release['body']
            : '';

        $name = isset($release['name']) && is_scalar($release['name'])
            ? sanitize_text_field((string) $release['name'])
            : '';

        $published_at = isset($release['published_at']) && is_scalar($release['published_at'])
            ? sanitize_text_field((string) $release['published_at'])
            : '';

        return [
            'tag' => $tag,
            'version' => $version,
            'name' => $name,
            'body' => $body,
            'html_url' => $html_url,
            'published_at' => $published_at,
            'prerelease' => $is_prerelease,
        ];
    }

    /**
     * Convert tag (v1.2.3) into version string (1.2.3).
     */
    private function normalize_tag_to_version($tag) {
        if (!is_string($tag) || '' === $tag) {
            return null;
        }

        if (1 !== preg_match('/^v?(\d+\.\d+\.\d+(?:-[0-9A-Za-z\.-]+)?)$/', $tag, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Build download package URL for a release tag.
     */
    private function build_package_url($tag) {
        return sprintf(
            'https://github.com/ercanatay/widget-visibility-descendants/archive/refs/tags/%s.zip',
            rawurlencode($tag)
        );
    }

    /**
     * Get release list from cache or GitHub API.
     */
    private function get_releases() {
        $cached = get_site_transient(self::RELEASE_CACHE_KEY);
        if (is_array($cached) && isset($cached['releases']) && is_array($cached['releases'])) {
            return $cached['releases'];
        }

        $request = $this->request_releases();
        // Sentinel: Implement negative caching for API failures (DoS prevention)
        // If request failed or returned empty, cache the failure for 15 minutes to prevent hammering the API.
        // Otherwise, cache success for the standard TTL (1 hour).
        $ttl = empty($request) ? 15 * MINUTE_IN_SECONDS : self::RELEASE_CACHE_TTL;

        set_site_transient(
            self::RELEASE_CACHE_KEY,
            [
                'releases' => $request,
                'fetched_at' => time(),
            ],
            $ttl
        );
        return $request;
    }

    /**
     * Request GitHub releases API payload.
     */
    private function request_releases() {
        $response = wp_remote_get(
            self::RELEASES_API_URL,
            [
                'timeout' => 15,
                'headers' => [
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => sprintf('WordPress/%s; %s', get_bloginfo('version'), home_url('/')),
                ],
            ]
        );

        if (is_wp_error($response)) {
            return [];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code < 200 || $status_code >= 300) {
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        if (!is_string($body) || '' === $body) {
            return [];
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * Read enabled/disabled updater setting.
     */
    private function is_updater_enabled() {
        if (!class_exists('WVD_Updater_Settings')) {
            return true;
        }

        return WVD_Updater_Settings::is_enabled();
    }

    /**
     * Read selected update channel from settings.
     */
    private function get_selected_channel() {
        if (!class_exists('WVD_Updater_Settings')) {
            return 'stable';
        }

        return WVD_Updater_Settings::get_channel();
    }
}
