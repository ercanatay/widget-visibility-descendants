<?php
/**
 * Frontend functionality for Widget Visibility with Descendants
 *
 * @package Widget_Visibility_Descendants
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend Class
 */
class WVD_Visibility_Frontend {

    /**
     * Cached term ancestors to avoid repeated taxonomy lookups.
     *
     * @var array<string, int[]>
     */
    private $term_ancestor_cache = [];

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('widget_display_callback', [$this, 'filter_widget_display'], 10, 3);
    }

    /**
     * Filter widget display based on visibility rules
     */
    public function filter_widget_display($instance, $widget, $args) {
        // Global bypass: skip all visibility rules when enabled
        $settings = get_option('wvd_settings', []);
        if (!empty($settings['global_bypass'])) {
            return $instance;
        }

        // No instance or no visibility rules
        if (!is_array($instance) || empty($instance['wvd_visibility']['rules'])) {
            return $instance;
        }

        $visibility = $instance['wvd_visibility'];
        $action = isset($visibility['action']) ? $visibility['action'] : 'show';
        $match_all = !empty($visibility['match_all']);
        $rules = is_array($visibility['rules']) ? $visibility['rules'] : [];
        $supported_rule_types = $this->get_supported_rule_types();

        // Ignore unsupported/legacy rule types before evaluating visibility.
        $rules = array_values(array_filter($rules, function($rule) use ($supported_rule_types) {
            return is_array($rule)
                && !empty($rule['type'])
                && in_array($rule['type'], $supported_rule_types, true);
        }));

        // Keep widget visible when no valid rules remain after filtering.
        if (empty($rules)) {
            return $instance;
        }

        // Evaluate all rules
        $results = [];
        foreach ($rules as $rule) {
            $results[] = $this->evaluate_rule($rule);
        }

        // Determine if conditions are met
        $conditions_met = $match_all ? !in_array(false, $results, true) : in_array(true, $results, true);

        // Apply action
        if ($action === 'show') {
            // Show if conditions met, hide otherwise
            return $conditions_met ? $instance : false;
        } else {
            // Hide if conditions met, show otherwise
            return $conditions_met ? false : $instance;
        }
    }

    /**
     * Supported visibility rule types.
     */
    private function get_supported_rule_types() {
        return [
            'page',
            'category',
            'post_type',
            'front_page',
            'blog',
            'archive',
            'search',
            '404',
            'single',
            'logged_in',
            'logged_out',
            'taxonomy',
            'user_role',
        ];
    }

    /**
     * Evaluate a single rule
     */
    private function evaluate_rule($rule) {
        $type = isset($rule['type']) ? $rule['type'] : '';
        $value = isset($rule['value']) ? $rule['value'] : '';
        $taxonomy = isset($rule['taxonomy']) ? sanitize_key((string) $rule['taxonomy']) : '';
        $values = isset($rule['values']) && is_array($rule['values']) ? $rule['values'] : [];
        $include_children = !empty($rule['include_children']);
        $include_descendants = !empty($rule['include_descendants']);

        switch ($type) {
            case 'page':
                return $this->evaluate_page_rule($value, $include_children, $include_descendants);

            case 'category':
                return $this->evaluate_category_rule($value, $include_children, $include_descendants);

            case 'post_type':
                return $this->evaluate_post_type_rule($value);

            case 'taxonomy':
                return $this->evaluate_taxonomy_rule($taxonomy, $value, $include_children, $include_descendants);

            case 'front_page':
                return is_front_page();

            case 'blog':
                return is_home();

            case 'archive':
                return is_archive();

            case 'search':
                return is_search();

            case '404':
                return is_404();

            case 'single':
                return is_single();

            case 'logged_in':
                return is_user_logged_in();

            case 'logged_out':
                return !is_user_logged_in();

            case 'user_role':
                return $this->evaluate_user_role_rule($values);

            default:
                return false;
        }
    }

    /**
     * Evaluate page rule with descendant support
     */
    private function evaluate_page_rule($page_id, $include_children, $include_descendants) {
        $page_id = absint($page_id);
        if ($page_id <= 0 || !is_page()) {
            return false;
        }

        $current_page_id = get_queried_object_id();

        // Exact match
        if ($current_page_id === $page_id) {
            return true;
        }

        // Include all descendants (grandchildren, great-grandchildren, etc.)
        if ($include_descendants) {
            $ancestors = get_post_ancestors($current_page_id);
            $ancestors = array_map('absint', $ancestors);
            return in_array($page_id, $ancestors, true);
        }

        // Include only direct children
        if ($include_children) {
            $current_page = get_post($current_page_id);
            return $current_page && absint($current_page->post_parent) === $page_id;
        }

        return false;
    }

    /**
     * Evaluate category rule with descendant support
     */
    private function evaluate_category_rule($cat_id, $include_children, $include_descendants) {
        $cat_id = absint($cat_id);
        if ($cat_id <= 0) {
            return false;
        }

        // Check if viewing category archive
        if (is_category()) {
            $current_cat = get_queried_object();

            // Security: Ensure valid term object to prevent property access on non-objects
            if (!($current_cat instanceof WP_Term)) {
                return false;
            }

            // Exact match
            if ($current_cat->term_id === $cat_id) {
                return true;
            }

            // Include descendants
            if ($include_descendants) {
                $ancestors = get_ancestors($current_cat->term_id, 'category');
                $ancestors = array_map('absint', $ancestors);
                return in_array($cat_id, $ancestors, true);
            }

            // Include children
            if ($include_children) {
                return absint($current_cat->parent) === $cat_id;
            }
        }

        // Check if viewing single post in category
        if (is_single()) {
            $post_id = get_queried_object_id();
            if (!$post_id) {
                return false;
            }

            $post_categories = wp_get_post_categories($post_id);
            if (is_wp_error($post_categories) || !is_array($post_categories) || empty($post_categories)) {
                return false;
            }

            $post_categories = array_map('absint', $post_categories);
            if (in_array($cat_id, $post_categories, true)) {
                return true;
            }

            // Check descendants
            if ($include_children || $include_descendants) {
                foreach ($post_categories as $post_cat_id) {
                    $ancestors = get_ancestors($post_cat_id, 'category');
                    $ancestors = array_map('absint', $ancestors);

                    if ($include_descendants && in_array($cat_id, $ancestors, true)) {
                        return true;
                    }

                    if ($include_children) {
                        $cat = get_category($post_cat_id);
                        // Security: Check for WP_Error and ensure object validity
                        if ($cat && !is_wp_error($cat) && absint($cat->parent) === $cat_id) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Evaluate post type rule
     */
    private function evaluate_post_type_rule($post_type) {
        if (!is_string($post_type) || $post_type === '' || !post_type_exists($post_type)) {
            return false;
        }

        if (is_singular($post_type)) {
            return true;
        }

        if (is_post_type_archive($post_type)) {
            return true;
        }

        return false;
    }

    /**
     * Evaluate custom taxonomy rule with descendant support.
     */
    private function evaluate_taxonomy_rule($taxonomy, $term_id, $include_children, $include_descendants) {
        if (!is_string($taxonomy) || $taxonomy === '' || !taxonomy_exists($taxonomy)) {
            return false;
        }

        $term_id = absint($term_id);
        if ($term_id <= 0) {
            return false;
        }

        if (is_tax($taxonomy)) {
            $current_term = get_queried_object();
            if (!($current_term instanceof WP_Term) || $current_term->taxonomy !== $taxonomy) {
                return false;
            }

            if ((int) $current_term->term_id === $term_id) {
                return true;
            }

            if ($include_descendants) {
                $ancestors = $this->get_term_ancestors($current_term->term_id, $taxonomy);
                if (in_array($term_id, $ancestors, true)) {
                    return true;
                }
            }

            if ($include_children && (int) $current_term->parent === $term_id) {
                return true;
            }
        }

        if (is_singular()) {
            $post_id = get_queried_object_id();
            if (!$post_id) {
                return false;
            }

            $post_terms = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'ids']);
            if (is_wp_error($post_terms) || !is_array($post_terms) || empty($post_terms)) {
                return false;
            }

            $post_terms = array_map('intval', $post_terms);
            if (in_array($term_id, $post_terms, true)) {
                return true;
            }

            if ($include_children || $include_descendants) {
                foreach ($post_terms as $post_term_id) {
                    if ($include_descendants) {
                        $ancestors = $this->get_term_ancestors($post_term_id, $taxonomy);
                        if (in_array($term_id, $ancestors, true)) {
                            return true;
                        }
                    }

                    if ($include_children) {
                        $post_term = get_term($post_term_id, $taxonomy);
                        if ($post_term instanceof WP_Term && !is_wp_error($post_term) && (int) $post_term->parent === $term_id) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Evaluate user role rule (any selected role).
     */
    private function evaluate_user_role_rule($selected_roles) {
        if (!is_user_logged_in() || !is_array($selected_roles) || empty($selected_roles)) {
            return false;
        }

        $user = wp_get_current_user();
        if (!($user instanceof WP_User) || empty($user->ID) || !is_array($user->roles)) {
            return false;
        }

        $user_roles = array_map('sanitize_key', $user->roles);
        foreach ($selected_roles as $selected_role) {
            if (!is_scalar($selected_role)) {
                continue;
            }

            $selected_role = sanitize_key((string) $selected_role);
            if ($selected_role !== '' && in_array($selected_role, $user_roles, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get cached term ancestors as integers.
     *
     * @return int[]
     */
    private function get_term_ancestors($term_id, $taxonomy) {
        $term_id = absint($term_id);
        $taxonomy = sanitize_key((string) $taxonomy);

        if ($term_id <= 0 || $taxonomy === '') {
            return [];
        }

        $cache_key = $taxonomy . ':' . $term_id;
        if (isset($this->term_ancestor_cache[$cache_key])) {
            return $this->term_ancestor_cache[$cache_key];
        }

        $ancestors = get_ancestors($term_id, $taxonomy, 'taxonomy');
        $ancestors = is_array($ancestors) ? array_map('intval', $ancestors) : [];

        $this->term_ancestor_cache[$cache_key] = $ancestors;
        return $ancestors;
    }
}
