<?php
/**
 * WooCommerce Order Admin customizations for Resellers.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Wc_Order_Admin {
	use Singleton;

	/**
	 * Register hooks.
	 */
	protected function __construct() {
		// Traditional WP Post-based orders
		add_filter( 'manage_edit-shop_order_columns', [ $this, 'add_reseller_column' ], 20 );
		add_action( 'manage_shop_order_custom_column', [ $this, 'render_reseller_column' ], 20, 2 );

		// High-Performance Order Storage (HPOS)
		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'add_reseller_column' ], 20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'render_reseller_column_hpos' ], 20, 2 );
		
		// Rename meta keys for display
		add_filter( 'woocommerce_order_item_display_meta_key', [ $this, 'rename_order_item_meta_keys' ], 10, 3 );
	}

	/**
	 * Add "Reseller Name" column to the orders list.
	 *
	 * @param array $columns Existing columns.
	 * @return array Updated columns.
	 */
	public function add_reseller_column( $columns ) {
		$new_columns = [];

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			// Add after the order status or order number
			if ( 'order_status' === $key || 'order_number' === $key ) {
				$new_columns['reseller_name'] = __( 'Reseller', 'reseller-management' );
			}
		}

		// Fallback if the insertion point wasn't found
		if ( ! isset( $new_columns['reseller_name'] ) ) {
			$new_columns['reseller_name'] = __( 'Reseller', 'reseller-management' );
		}

		return $new_columns;
	}

	/**
	 * Render the reseller column content (Traditional orders).
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_reseller_column( $column, $post_id ) {
		if ( 'reseller_name' !== $column ) {
			return;
		}

		$order = wc_get_order( $post_id );
		if ( ! $order ) {
			return;
		}

		$this->display_reseller_name( $order );
	}

	/**
	 * Render the reseller column content (HPOS).
	 *
	 * @param string    $column Column key.
	 * @param \WC_Order $order  Order object.
	 * @return void
	 */
	public function render_reseller_column_hpos( $column, $order ) {
		if ( 'reseller_name' !== $column ) {
			return;
		}

		$this->display_reseller_name( $order );
	}

	/**
	 * Helper to display the reseller name.
	 *
	 * @param \WC_Order $order Order object.
	 * @return void
	 */
	private function display_reseller_name( $order ) {
		$reseller_id = $order->get_meta( '_assigned_reseller_id' );

		if ( ! $reseller_id ) {
			echo '<span class="na">&ndash;</span>';
			return;
		}

		$reseller = get_user_by( 'id', $reseller_id );
		if ( ! $reseller ) {
			echo '<span class="na">' . esc_html__( 'Unknown Reseller', 'reseller-management' ) . '</span>';
			return;
		}

		$reseller_name = get_user_meta( $reseller_id, '_reseller_business_name', true );
		if ( empty( $reseller_name ) ) {
			$reseller_name = $reseller->display_name;
		}

		$edit_link = admin_url( 'admin.php?page=reseller-hub-user-view&reseller_id=' . $reseller_id );

		echo '<a href="' . esc_url( $edit_link ) . '"><strong>' . esc_html( $reseller_name ) . '</strong></a>';
	}

	/**
	 * Rename order item meta keys for display (e.g., _resale_price -> Resale Price).
	 *
	 * @param string          $display_key Display key.
	 * @param \WC_Meta_Data   $meta        Meta data object.
	 * @param \WC_Order_Item  $item        Order item object.
	 * @return string Updated display key.
	 */
	public function rename_order_item_meta_keys( $display_key, $meta, $item ) {
		if ( '_resale_price' === $display_key ) {
			return __( 'Resale Price', 'reseller-management' );
		}

		if ( '_base_price' === $display_key ) {
			return __( 'Base Price', 'reseller-management' );
		}

		return $display_key;
	}
}
