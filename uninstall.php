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

$settings = get_option( 'wvd_settings', [] );

if ( ! empty( $settings['delete_data_on_uninstall'] ) ) {
	// Remove wvd_visibility from all widget instances.
	$sidebars = get_option( 'sidebars_widgets', [] );
	foreach ( $sidebars as $sidebar_id => $widgets ) {
		if ( ! is_array( $widgets ) ) {
			continue;
		}
		foreach ( $widgets as $widget_id ) {
			// widget_id format: widget-type-N
			if ( ! preg_match( '/^(.+)-(\d+)$/', $widget_id, $m ) ) {
				continue;
			}
			$option    = 'widget_' . $m[1];
			$instances = get_option( $option, [] );
			if ( is_array( $instances ) && isset( $instances[ (int) $m[2] ]['wvd_visibility'] ) ) {
				unset( $instances[ (int) $m[2] ]['wvd_visibility'] );
				update_option( $option, $instances );
			}
		}
	}
}

// Always remove plugin settings on uninstall.
delete_option( 'wvd_settings' );
