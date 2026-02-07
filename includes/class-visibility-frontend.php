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
     * Constructor
     */
    public function __construct() {
        add_filter('widget_display_callback', [$this, 'filter_widget_display'], 10, 3);
    }

    /**
     * Filter widget display based on visibility rules
     */
    public function filter_widget_display($instance, $widget, $args) {
        // No instance or no visibility rules
        if (!$instance || empty($instance['wvd_visibility']['rules'])) {
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
        ];
    }

    /**
     * Evaluate a single rule
     */
    private function evaluate_rule($rule) {
        $type = isset($rule['type']) ? $rule['type'] : '';
        $value = isset($rule['value']) ? $rule['value'] : '';
        $include_children = !empty($rule['include_children']);
        $include_descendants = !empty($rule['include_descendants']);

        switch ($type) {
            case 'page':
                return $this->evaluate_page_rule($value, $include_children, $include_descendants);

            case 'category':
                return $this->evaluate_category_rule($value, $include_children, $include_descendants);

            case 'post_type':
                return $this->evaluate_post_type_rule($value);

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

            default:
                return false;
        }
    }

    /**
     * Evaluate page rule with descendant support
     */
    private function evaluate_page_rule($page_id, $include_children, $include_descendants) {
        if (!is_page()) {
            return false;
        }

        $current_page_id = get_queried_object_id();
        $page_id = intval($page_id);

        // Exact match
        if ($current_page_id === $page_id) {
            return true;
        }

        // Include all descendants (grandchildren, great-grandchildren, etc.)
        if ($include_descendants) {
            $ancestors = get_post_ancestors($current_page_id);
            // Convert all ancestor IDs to integers for reliable comparison
            $ancestors = array_map('intval', $ancestors);
            return in_array($page_id, $ancestors, true);
        }

        // Include only direct children
        if ($include_children) {
            $current_page = get_post($current_page_id);
            return $current_page && intval($current_page->post_parent) === $page_id;
        }

        return false;
    }

    /**
     * Evaluate category rule with descendant support
     */
    private function evaluate_category_rule($cat_id, $include_children, $include_descendants) {
        $cat_id = intval($cat_id);

        // Check if viewing category archive
        if (is_category()) {
            $current_cat = get_queried_object();

            // Exact match
            if ($current_cat->term_id === $cat_id) {
                return true;
            }

            // Include descendants
            if ($include_descendants) {
                $ancestors = get_ancestors($current_cat->term_id, 'category');
                $ancestors = array_map('intval', $ancestors);
                return in_array($cat_id, $ancestors, true);
            }

            // Include children
            if ($include_children) {
                return intval($current_cat->parent) === $cat_id;
            }
        }

        // Check if viewing single post in category
        if (is_single()) {
            $post_categories = wp_get_post_categories(get_the_ID());

            if (in_array($cat_id, $post_categories, true)) {
                return true;
            }

            // Check descendants
            if ($include_children || $include_descendants) {
                foreach ($post_categories as $post_cat_id) {
                    $ancestors = get_ancestors($post_cat_id, 'category');
                    $ancestors = array_map('intval', $ancestors);

                    if ($include_descendants && in_array($cat_id, $ancestors, true)) {
                        return true;
                    }

                    if ($include_children) {
                        $cat = get_category($post_cat_id);
                        if ($cat && intval($cat->parent) === $cat_id) {
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
        if (is_singular($post_type)) {
            return true;
        }

        if (is_post_type_archive($post_type)) {
            return true;
        }

        return false;
    }
}
