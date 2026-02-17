<?php
/**
 * Uninstall script for Widget Visibility with Descendants
 *
 * @package WidgetVisibilityDescendants
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// No persistent data to clean up.
// Widget visibility settings are stored within individual widget instances
// and are removed automatically when widgets are deleted.
