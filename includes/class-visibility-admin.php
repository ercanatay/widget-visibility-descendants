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

        $taxonomies = $this->get_hierarchical_taxonomies();

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
            'taxonomies' => $taxonomies,
            'taxonomyTerms' => $this->get_taxonomy_terms($taxonomies),
            'roles' => $this->get_user_roles(),
            'i18n' => [
                'visibility' => __('Visibility', 'widget-visibility-with-descendants'),
                'show' => __('Show', 'widget-visibility-with-descendants'),
                'hide' => __('Hide', 'widget-visibility-with-descendants'),
                'if' => __('if', 'widget-visibility-with-descendants'),
                'is' => __('is', 'widget-visibility-with-descendants'),
                'page' => __('Page', 'widget-visibility-with-descendants'),
                'category' => __('Category', 'widget-visibility-with-descendants'),
                'postType' => __('Post Type', 'widget-visibility-with-descendants'),
                'taxonomy' => __('Taxonomy', 'widget-visibility-with-descendants'),
                'userRole' => __('User Role', 'widget-visibility-with-descendants'),
                'frontPage' => __('Front Page', 'widget-visibility-with-descendants'),
                'blog' => __('Blog', 'widget-visibility-with-descendants'),
                'archive' => __('Archive', 'widget-visibility-with-descendants'),
                'search' => __('Search', 'widget-visibility-with-descendants'),
                'notFound' => __('404', 'widget-visibility-with-descendants'),
                'single' => __('Single Post', 'widget-visibility-with-descendants'),
                'loggedIn' => __('Logged In', 'widget-visibility-with-descendants'),
                'loggedOut' => __('Logged Out', 'widget-visibility-with-descendants'),
                'selectPostType' => __('Select a post type...', 'widget-visibility-with-descendants'),
                'selectTaxonomy' => __('Select a taxonomy...', 'widget-visibility-with-descendants'),
                'selectTerm' => __('Select a term...', 'widget-visibility-with-descendants'),
                'selectRoles' => __('Select one or more roles...', 'widget-visibility-with-descendants'),
                'configured' => __('Configured', 'widget-visibility-with-descendants'),
                'includeChildren' => __('Include children', 'widget-visibility-with-descendants'),
                'includeDescendants' => __('Include all descendants', 'widget-visibility-with-descendants'),
                'matchAll' => __('Match all conditions', 'widget-visibility-with-descendants'),
                'addCondition' => __('Add condition', 'widget-visibility-with-descendants'),
                'remove' => __('Remove', 'widget-visibility-with-descendants'),
                'done' => __('Done', 'widget-visibility-with-descendants'),
                'delete' => __('Delete', 'widget-visibility-with-descendants'),
                'selectPage' => __('Select a page...', 'widget-visibility-with-descendants'),
                'selectCategory' => __('Select a category...', 'widget-visibility-with-descendants'),
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

        // Build a set of parent IDs from the fetched pages to avoid N+1 queries.
        $parent_ids = [];
        foreach ($pages as $page) {
            if ($page->post_parent > 0) {
                $parent_ids[$page->post_parent] = true;
            }
        }

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
                'hasChildren' => isset($parent_ids[$page->ID]),
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

        // Build lookup maps to avoid N+1 queries and per-category parent walks.
        $cat_by_id = [];
        $parent_ids = [];
        foreach ($categories as $cat) {
            $cat_by_id[$cat->term_id] = $cat;
            if ($cat->parent > 0) {
                $parent_ids[$cat->parent] = true;
            }
        }

        $options = [];
        foreach ($categories as $cat) {
            // Calculate depth via lookup map with a guard against circular references.
            $depth = 0;
            $parent = $cat->parent;
            $seen = [];
            while ($parent > 0 && isset($cat_by_id[$parent]) && !isset($seen[$parent])) {
                $seen[$parent] = true;
                $depth++;
                $parent = $cat_by_id[$parent]->parent;
            }
            $prefix = str_repeat('— ', $depth);
            // Sanitize category names before localizing to JS to prevent XSS.
            $name = sanitize_text_field($cat->name);
            $options[] = [
                'id' => $cat->term_id,
                'title' => $prefix . $name,
                'parent' => $cat->parent,
                'hasChildren' => isset($parent_ids[$cat->term_id]),
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
     * Get hierarchical public taxonomies, excluding built-in category.
     */
    private function get_hierarchical_taxonomies() {
        $taxonomies = get_taxonomies([
            'public' => true,
            'hierarchical' => true,
        ], 'objects');

        $options = [];
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->name === 'category') {
                continue;
            }

            $label = isset($taxonomy->labels->singular_name)
                ? $taxonomy->labels->singular_name
                : $taxonomy->label;

            $options[] = [
                'id' => sanitize_key($taxonomy->name),
                'title' => sanitize_text_field($label),
            ];
        }

        usort($options, static function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        return $options;
    }

    /**
     * Get taxonomy terms grouped by taxonomy slug.
     */
    private function get_taxonomy_terms($taxonomies) {
        $terms_by_taxonomy = [];

        if (!is_array($taxonomies)) {
            return $terms_by_taxonomy;
        }

        foreach ($taxonomies as $taxonomy_option) {
            if (empty($taxonomy_option['id']) || !is_scalar($taxonomy_option['id'])) {
                continue;
            }

            $taxonomy = sanitize_key((string) $taxonomy_option['id']);
            if (!$this->is_valid_hierarchical_taxonomy($taxonomy)) {
                continue;
            }

            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC',
            ]);

            if (is_wp_error($terms) || !is_array($terms)) {
                $terms_by_taxonomy[$taxonomy] = [];
                continue;
            }

            $options = [];
            foreach ($terms as $term) {
                if (!($term instanceof WP_Term)) {
                    continue;
                }

                $depth = count(get_ancestors($term->term_id, $taxonomy, 'taxonomy'));
                $prefix = str_repeat('— ', $depth);

                $children = get_terms([
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                    'parent' => $term->term_id,
                    'number' => 1,
                    'fields' => 'ids',
                ]);

                $options[] = [
                    'id' => $term->term_id,
                    'title' => $prefix . sanitize_text_field($term->name),
                    'parent' => $term->parent,
                    'hasChildren' => is_array($children) && !empty($children),
                ];
            }

            $terms_by_taxonomy[$taxonomy] = $options;
        }

        return $terms_by_taxonomy;
    }

    /**
     * Get user roles.
     */
    private function get_user_roles() {
        $wp_roles = wp_roles();
        $options = [];

        if (!($wp_roles instanceof WP_Roles) || !is_array($wp_roles->roles)) {
            return $options;
        }

        foreach ($wp_roles->roles as $slug => $role_data) {
            if (!is_string($slug) || !is_array($role_data)) {
                continue;
            }

            $label = isset($role_data['name']) && is_string($role_data['name'])
                ? translate_user_role($role_data['name'])
                : $slug;

            $options[] = [
                'id' => sanitize_key($slug),
                'title' => sanitize_text_field($label),
            ];
        }

        usort($options, static function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        return $options;
    }

    /**
     * Get available role slugs.
     */
    private function get_role_slugs() {
        $roles = $this->get_user_roles();
        $role_slugs = [];

        foreach ($roles as $role) {
            if (!empty($role['id']) && is_scalar($role['id'])) {
                $role_slugs[] = sanitize_key((string) $role['id']);
            }
        }

        return array_values(array_unique($role_slugs));
    }

    /**
     * Validate hierarchical public taxonomy slug.
     */
    private function is_valid_hierarchical_taxonomy($taxonomy) {
        if (!is_string($taxonomy) || $taxonomy === '' || $taxonomy === 'category') {
            return false;
        }

        $taxonomy_obj = get_taxonomy($taxonomy);

        if (!is_object($taxonomy_obj)) {
            return false;
        }

        return !empty($taxonomy_obj->public) && !empty($taxonomy_obj->hierarchical);
    }

    /**
     * Render visibility UI in widget form
     */
    public function render_visibility_ui($widget, $return, $instance) {
        // Security: Only render UI for users who can manage widgets
        if (!current_user_can('edit_theme_options')) {
            return;
        }

        $visibility = isset($instance['wvd_visibility']) ? $instance['wvd_visibility'] : [];
        $widget_id = $widget->id;
        ?>
        <div class="wvd-visibility-wrapper" data-widget-id="<?php echo esc_attr($widget_id); ?>">
            <p class="wvd-visibility-toggle">
                <button type="button" class="button wvd-visibility-button">
                    <?php esc_html_e('Visibility', 'widget-visibility-with-descendants'); ?>
                </button>
                <?php if (!empty($visibility['rules'])): ?>
                    <span class="wvd-visibility-status wvd-has-rules"><?php esc_html_e('Configured', 'widget-visibility-with-descendants'); ?></span>
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
        // Defensive check: avoid offset assignment on non-array instances.
        if (!is_array($instance)) {
            return $instance;
        }

        // Security: Verify user has permission to manage widgets
        if (!current_user_can('edit_theme_options')) {
            // Restore old visibility settings if present to prevent data loss
            if (isset($old_instance['wvd_visibility'])) {
                $instance['wvd_visibility'] = $old_instance['wvd_visibility'];
            }
            return $instance;
        }

        if (isset($new_instance['wvd_visibility'])) {
            $data = $new_instance['wvd_visibility'];
            if (is_string($data)) {
                $data = json_decode(wp_unslash($data), true);
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
     * - Validate taxonomy and role payloads
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
            'taxonomy', 'user_role',
        ];

        // Maximum number of rules to prevent DoS
        $max_rules = 50;

        // Maximum value length to prevent database bloat
        $max_value_length = 100;

        // Maximum number of role values in one rule
        $max_role_values = 20;

        $valid_post_types = array_map('sanitize_key', get_post_types(['public' => true], 'names'));
        $valid_roles = $this->get_role_slugs();

        if (!empty($data['rules']) && is_array($data['rules'])) {
            $count = 0;
            foreach ($data['rules'] as $rule) {
                // Enforce rule limit
                if ($count >= $max_rules) {
                    break;
                }

                if (!is_array($rule) || !isset($rule['type']) || !is_scalar($rule['type'])) {
                    continue;
                }

                // Sanitize and validate type against whitelist
                $type = sanitize_key((string) $rule['type']);
                if (!in_array($type, $allowed_types, true)) {
                    continue;
                }

                // Sanitize and limit value length
                $value = '';
                if (isset($rule['value']) && is_scalar($rule['value'])) {
                    $value = sanitize_text_field((string) $rule['value']);
                }
                if (strlen($value) > $max_value_length) {
                    $value = substr($value, 0, $max_value_length);
                }

                $sanitized_rule = [
                    'type' => $type,
                    'include_children' => !empty($rule['include_children']),
                    'include_descendants' => !empty($rule['include_descendants']),
                ];

                if ('user_role' === $type) {
                    $candidate_roles = [];

                    if (isset($rule['values']) && is_array($rule['values'])) {
                        $candidate_roles = $rule['values'];
                    } elseif ($value !== '') {
                        // Backward-compatible fallback if a single scalar role value is provided.
                        $candidate_roles = [$value];
                    }

                    $roles = [];
                    foreach ($candidate_roles as $candidate_role) {
                        if (count($roles) >= $max_role_values) {
                            break;
                        }

                        if (!is_scalar($candidate_role)) {
                            continue;
                        }

                        $role_slug = sanitize_key((string) $candidate_role);
                        if ($role_slug !== '' && in_array($role_slug, $valid_roles, true)) {
                            $roles[] = $role_slug;
                        }
                    }

                    $roles = array_values(array_unique($roles));
                    if (empty($roles)) {
                        continue;
                    }

                    $sanitized_rule['values'] = $roles;
                    $sanitized_rule['value'] = '';
                    $sanitized_rule['include_children'] = false;
                    $sanitized_rule['include_descendants'] = false;
                    $sanitized['rules'][] = $sanitized_rule;
                    $count++;
                    continue;
                }

                if ('taxonomy' === $type) {
                    if (!isset($rule['taxonomy']) || !is_scalar($rule['taxonomy'])) {
                        continue;
                    }

                    $taxonomy = sanitize_key((string) $rule['taxonomy']);
                    if (!$this->is_valid_hierarchical_taxonomy($taxonomy)) {
                        continue;
                    }

                    $term_id = absint($value);
                    if ($term_id <= 0) {
                        continue;
                    }

                    $term = get_term($term_id, $taxonomy);
                    if (!($term instanceof WP_Term) || is_wp_error($term)) {
                        continue;
                    }

                    $sanitized_rule['taxonomy'] = $taxonomy;
                    $sanitized_rule['value'] = (string) $term_id;
                    $sanitized['rules'][] = $sanitized_rule;
                    $count++;
                    continue;
                }

                if ('post_type' === $type) {
                    $value = sanitize_key($value);
                    if ($value === '' || !in_array($value, $valid_post_types, true)) {
                        continue;
                    }

                    $sanitized_rule['value'] = $value;
                    $sanitized_rule['include_children'] = false;
                    $sanitized_rule['include_descendants'] = false;
                    $sanitized['rules'][] = $sanitized_rule;
                    $count++;
                    continue;
                }

                if (in_array($type, ['page', 'category'], true)) {
                    $entity_id = absint($value);
                    if ($entity_id <= 0) {
                        continue;
                    }

                    $sanitized_rule['value'] = (string) $entity_id;
                    $sanitized['rules'][] = $sanitized_rule;
                    $count++;
                    continue;
                }

                $sanitized_rule['value'] = $value;
                $sanitized_rule['include_children'] = false;
                $sanitized_rule['include_descendants'] = false;
                $sanitized['rules'][] = $sanitized_rule;
                $count++;
            }
        }

        return $sanitized;
    }
}
