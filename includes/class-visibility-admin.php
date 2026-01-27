<?php
/**
 * Admin functionality for Widget Visibility with Descendants
 *
 * @package Widget_Visibility_Descendants
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Class
 */
class WVD_Visibility_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('in_widget_form', [$this, 'render_visibility_ui'], 10, 3);
        add_filter('widget_update_callback', [$this, 'save_visibility_settings'], 10, 4);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        if ('widgets.php' !== $hook && 'customize.php' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'wvd-admin-css',
            WVD_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WVD_VERSION
        );

        wp_enqueue_script(
            'wvd-admin-js',
            WVD_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WVD_VERSION,
            true
        );

        // Localize script
        wp_localize_script('wvd-admin-js', 'wvdData', [
            'pages' => $this->get_hierarchical_pages(),
            'categories' => $this->get_categories(),
            'postTypes' => $this->get_post_types(),
            'i18n' => [
                'visibility' => __('Visibility', 'widget-visibility-descendants'),
                'show' => __('Show', 'widget-visibility-descendants'),
                'hide' => __('Hide', 'widget-visibility-descendants'),
                'if' => __('if', 'widget-visibility-descendants'),
                'is' => __('is', 'widget-visibility-descendants'),
                'page' => __('Page', 'widget-visibility-descendants'),
                'category' => __('Category', 'widget-visibility-descendants'),
                'postType' => __('Post Type', 'widget-visibility-descendants'),
                'taxonomy' => __('Taxonomy', 'widget-visibility-descendants'),
                'author' => __('Author', 'widget-visibility-descendants'),
                'includeChildren' => __('Include children', 'widget-visibility-descendants'),
                'includeDescendants' => __('Include all descendants', 'widget-visibility-descendants'),
                'matchAll' => __('Match all conditions', 'widget-visibility-descendants'),
                'addCondition' => __('Add condition', 'widget-visibility-descendants'),
                'remove' => __('Remove', 'widget-visibility-descendants'),
                'done' => __('Done', 'widget-visibility-descendants'),
                'delete' => __('Delete', 'widget-visibility-descendants'),
                'selectPage' => __('Select a page...', 'widget-visibility-descendants'),
                'selectCategory' => __('Select a category...', 'widget-visibility-descendants'),
            ]
        ]);
    }

    /**
     * Get hierarchical pages with depth indicator
     */
    private function get_hierarchical_pages() {
        $pages = get_pages([
            'sort_column' => 'menu_order,post_title',
            'hierarchical' => true,
        ]);

        $options = [];
        foreach ($pages as $page) {
            $depth = count(get_post_ancestors($page->ID));
            $prefix = str_repeat('— ', $depth);
            // Sanitize titles before sending to the admin UI to avoid XSS via stored content.
            $title = sanitize_text_field($page->post_title);
            $options[] = [
                'id' => $page->ID,
                'title' => $prefix . $title,
                'parent' => $page->post_parent,
                'hasChildren' => (bool) get_children(['post_parent' => $page->ID, 'post_type' => 'page', 'numberposts' => 1]),
            ];
        }

        return $options;
    }

    /**
     * Get categories
     */
    private function get_categories() {
        $categories = get_categories([
            'hide_empty' => false,
            'hierarchical' => true,
        ]);

        $options = [];
        foreach ($categories as $cat) {
            $depth = 0;
            $parent = $cat->parent;
            while ($parent > 0) {
                $depth++;
                $parent_cat = get_category($parent);
                $parent = $parent_cat ? $parent_cat->parent : 0;
            }
            $prefix = str_repeat('— ', $depth);
            // Sanitize category names before localizing to JS to prevent XSS.
            $name = sanitize_text_field($cat->name);
            $options[] = [
                'id' => $cat->term_id,
                'title' => $prefix . $name,
                'parent' => $cat->parent,
                'hasChildren' => (bool) get_categories(['parent' => $cat->term_id, 'hide_empty' => false, 'number' => 1]),
            ];
        }

        return $options;
    }

    /**
     * Get public post types
     */
    private function get_post_types() {
        $post_types = get_post_types(['public' => true], 'objects');
        $options = [];

        foreach ($post_types as $pt) {
            if ($pt->name === 'attachment') continue;
            $options[] = [
                'id' => $pt->name,
                'title' => sanitize_text_field($pt->labels->singular_name),
            ];
        }

        return $options;
    }

    /**
     * Render visibility UI in widget form
     */
    public function render_visibility_ui($widget, $return, $instance) {
        $visibility = isset($instance['wvd_visibility']) ? $instance['wvd_visibility'] : [];
        $widget_id = $widget->id;
        ?>
        <div class="wvd-visibility-wrapper" data-widget-id="<?php echo esc_attr($widget_id); ?>">
            <p class="wvd-visibility-toggle">
                <button type="button" class="button wvd-visibility-button">
                    <?php esc_html_e('Visibility', 'widget-visibility-descendants'); ?>
                </button>
                <?php if (!empty($visibility['rules'])): ?>
                    <span class="wvd-visibility-status wvd-has-rules"><?php esc_html_e('Configured', 'widget-visibility-descendants'); ?></span>
                <?php endif; ?>
            </p>

            <div class="wvd-visibility-panel" style="display: none;">
                <input type="hidden"
                       name="<?php echo esc_attr($widget->get_field_name('wvd_visibility')); ?>"
                       class="wvd-visibility-data"
                       value="<?php echo esc_attr(wp_json_encode($visibility)); ?>">

                <div class="wvd-visibility-content">
                    <!-- JavaScript will render the UI here -->
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save visibility settings
     */
    public function save_visibility_settings($instance, $new_instance, $old_instance, $widget) {
        // Security: Verify user has permission to manage widgets
        if (!current_user_can('edit_theme_options')) {
            return $instance;
        }

        if (isset($new_instance['wvd_visibility'])) {
            $data = $new_instance['wvd_visibility'];
            if (is_string($data)) {
                $data = json_decode(stripslashes($data), true);
            }
            $instance['wvd_visibility'] = $this->sanitize_visibility_data($data);
        }
        return $instance;
    }

    /**
     * Sanitize visibility data
     *
     * Security hardening:
     * - Whitelist allowed rule types to prevent injection
     * - Limit rules to 50 max to prevent DoS/resource exhaustion
     * - Limit value length to 100 characters to prevent database bloat
     */
    private function sanitize_visibility_data($data) {
        if (!is_array($data)) {
            return [];
        }

        $sanitized = [
            'action' => isset($data['action']) && in_array($data['action'], ['show', 'hide'], true) ? $data['action'] : 'show',
            'match_all' => !empty($data['match_all']),
            'rules' => [],
        ];

        // Whitelist of allowed rule types for security
        $allowed_types = [
            'page', 'category', 'post_type', 'front_page', 'blog',
            'archive', 'search', '404', 'single', 'logged_in', 'logged_out',
            'taxonomy', 'author'
        ];

        // Maximum number of rules to prevent DoS
        $max_rules = 50;

        // Maximum value length to prevent database bloat
        $max_value_length = 100;

        if (!empty($data['rules']) && is_array($data['rules'])) {
            $count = 0;
            foreach ($data['rules'] as $rule) {
                // Enforce rule limit
                if ($count >= $max_rules) {
                    break;
                }

                if (!isset($rule['type']) || !isset($rule['value'])) {
                    continue;
                }

                // Sanitize and validate type against whitelist
                $type = sanitize_key($rule['type']);
                if (!in_array($type, $allowed_types, true)) {
                    continue;
                }

                // Sanitize and limit value length
                $value = sanitize_text_field($rule['value']);
                if (strlen($value) > $max_value_length) {
                    $value = substr($value, 0, $max_value_length);
                }

                $sanitized_rule = [
                    'type' => $type,
                    'value' => $value,
                    'include_children' => !empty($rule['include_children']),
                    'include_descendants' => !empty($rule['include_descendants']),
                ];

                $sanitized['rules'][] = $sanitized_rule;
                $count++;
            }
        }

        return $sanitized;
    }
}
