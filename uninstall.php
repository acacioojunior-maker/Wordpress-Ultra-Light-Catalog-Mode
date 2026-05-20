<?php
/**
 * Runs only when the plugin is permanently deleted from WordPress.
 * NOT triggered on deactivation or update — only on "Delete" after deactivation.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Convert any remaining rfq orders to pending before removing plugin data
if ( function_exists( 'wc_get_orders' ) ) {
	$rfq_orders = wc_get_orders( array(
		'status' => 'rfq',
		'limit'  => -1,
		'return' => 'ids',
	) );
	foreach ( $rfq_orders as $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->set_status( 'pending' );
			$order->save();
		}
	}
}

// Remove plugin options
delete_option( 'confiar_catalog_mode_enabled' );
delete_option( 'confiar_catalog_mode_button_text' );
delete_option( 'confiar_catalog_mode_notification_text' );
