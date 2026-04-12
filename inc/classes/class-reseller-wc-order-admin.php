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
		add_filter( 'manage_edit-shop_order_columns', [ $this, 'add_print_invoice_column' ], 25 );
		add_action( 'manage_shop_order_custom_column', [ $this, 'render_print_invoice_column' ], 25, 2 );

		// High-Performance Order Storage (HPOS)
		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'add_reseller_column' ], 20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'render_reseller_column_hpos' ], 20, 2 );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'add_print_invoice_column' ], 25 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'render_print_invoice_column_hpos' ], 25, 2 );
		
		// Rename meta keys for display
		add_filter( 'woocommerce_order_item_display_meta_key', [ $this, 'rename_order_item_meta_keys' ], 10, 3 );

		// totals customizations
		add_action( 'woocommerce_admin_order_totals_after_shipping', [ $this, 'render_paid_and_due_amount_rows' ] );

		// Bulk Actions
		add_filter( 'bulk_actions-edit-shop_order', [ $this, 'add_bulk_actions' ], 20 );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', [ $this, 'add_bulk_actions' ], 20 );
		add_filter( 'handle_bulk_actions-edit-shop_order', [ $this, 'handle_bulk_actions' ], 20, 3 );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', [ $this, 'handle_bulk_actions' ], 20, 3 );
		add_action( 'admin_notices', [ $this, 'bulk_admin_notices' ] );
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
	 * Add "Print" column for invoice (HPOS + legacy lists).
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_print_invoice_column( $columns ) {
		if ( isset( $columns['rm_print_invoice'] ) ) {
			return $columns;
		}

		// HPOS uses wc_actions; legacy list may use order_actions.
		$before_keys = [ 'wc_actions', 'order_actions' ];
		$new_columns   = [];
		$inserted      = false;

		foreach ( $columns as $key => $label ) {
			if ( ! $inserted && in_array( $key, $before_keys, true ) ) {
				$new_columns['rm_print_invoice'] = __( 'Invoice', 'reseller-management' );
				$inserted                        = true;
			}
			$new_columns[ $key ] = $label;
		}

		if ( ! $inserted ) {
			$new_columns['rm_print_invoice'] = __( 'Invoice', 'reseller-management' );
		}

		return $new_columns;
	}

	/**
	 * Build admin URL to full-page invoice (same template as reseller dashboard).
	 *
	 * @param int $order_id Order ID.
	 * @return string
	 */
	private function get_admin_invoice_print_url( $order_id ) {
		return add_query_arg(
			[
				'rm_action' => 'admin_print_invoice',
				'order_id'  => (int) $order_id,
				'nonce'     => wp_create_nonce( 'rm_admin_print_invoice_' . (int) $order_id ),
			],
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Render print column (Traditional orders).
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_print_invoice_column( $column, $post_id ) {
		if ( 'rm_print_invoice' !== $column ) {
			return;
		}

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			echo '&mdash;';
			return;
		}

		$order = wc_get_order( $post_id );
		if ( ! $order ) {
			echo '&mdash;';
			return;
		}

		$url = $this->get_admin_invoice_print_url( $order->get_id() );
		printf(
			'<a href="%1$s" class="button button-small" target="_blank" rel="noopener noreferrer" aria-label="%2$s">%3$s</a>',
			esc_url( $url ),
			esc_attr(
				sprintf(
					/* translators: %s: order number */
					__( 'Print invoice for order %s', 'reseller-management' ),
					$order->get_order_number()
				)
			),
			esc_html__( 'Print', 'reseller-management' )
		);
	}

	/**
	 * Render print column (HPOS).
	 *
	 * @param string    $column Column key.
	 * @param \WC_Order $order  Order object.
	 * @return void
	 */
	public function render_print_invoice_column_hpos( $column, $order ) {
		if ( 'rm_print_invoice' !== $column ) {
			return;
		}

		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			echo '&mdash;';
			return;
		}

		$url = $this->get_admin_invoice_print_url( $order->get_id() );
		printf(
			'<a href="%1$s" class="button button-small" target="_blank" rel="noopener noreferrer" aria-label="%2$s">%3$s</a>',
			esc_url( $url ),
			esc_attr(
				sprintf(
					/* translators: %s: order number */
					__( 'Print invoice for order %s', 'reseller-management' ),
					$order->get_order_number()
				)
			),
			esc_html__( 'Print', 'reseller-management' )
		);
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

	/**
	 * Render "Paid" and "Due Amount" rows in the totals section.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function render_paid_and_due_amount_rows( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$reseller_id = $order->get_meta( '_assigned_reseller_id' );
		if ( ! $reseller_id ) {
			return;
		}

		$paid_amount = (float) $order->get_meta( '_paid_amount' );

		?>
		<tr class="rm-paid-row">
			<td class="label"><?php esc_html_e( 'Advance Paid', 'reseller-management' ); ?>:</td>
			<td width="1%"></td>
			<td class="total"><?php echo wc_price( $paid_amount, array( 'currency' => $order->get_currency() ) ); ?></td>
		</tr>
		<tr class="rm-separator-row">
			<td colspan="3"><hr></td>
		</tr>
		<?php
	}

	/**
	 * Add custom order statuses to bulk actions dropdown.
	 *
	 * @param array $actions Existing bulk actions.
	 * @return array Updated bulk actions.
	 */
	public function add_bulk_actions( $actions ) {
		$statuses = wc_get_order_statuses();

		foreach ( $statuses as $status => $label ) {
			$action_key = 'mark_' . str_replace( 'wc-', '', $status );
			
			// If already exists (core WooCommerce actions), skip or overwrite if needed
			if ( ! isset( $actions[ $action_key ] ) ) {
				$actions[ $action_key ] = sprintf( __( 'Change status to %s', 'reseller-management' ), $label );
			}
		}

		return $actions;
	}

	/**
	 * Handle custom bulk actions.
	 *
	 * @param string $redirect_to The redirect URL.
	 * @param string $action      The action being taken.
	 * @param array  $ids         The array of IDs.
	 * @return string The redirect URL with extra args.
	 */
	public function handle_bulk_actions( $redirect_to, $action, $ids ) {
		if ( strpos( $action, 'mark_' ) !== 0 ) {
			return $redirect_to;
		}

		$status = 'wc-' . str_replace( 'mark_', '', $action );
		$valid_statuses = wc_get_order_statuses();

		if ( ! isset( $valid_statuses[ $status ] ) ) {
			return $redirect_to;
		}

		$changed = 0;

		foreach ( $ids as $id ) {
			$order = wc_get_order( $id );
			if ( $order ) {
				$order->update_status( $status, __( 'Bulk status update by admin.', 'reseller-management' ) );
				$changed++;
			}
		}

		$redirect_to = add_query_arg(
			[
				'rm_bulk_updated' => $changed,
				'rm_bulk_action'  => $action,
			],
			$redirect_to
		);

		return $redirect_to;
	}

	/**
	 * Display admin notice after bulk update.
	 *
	 * @return void
	 */
	public function bulk_admin_notices() {
		if ( empty( $_REQUEST['rm_bulk_updated'] ) ) {
			return;
		}

		$count  = (int) $_REQUEST['rm_bulk_updated'];
		$action = sanitize_text_field( $_REQUEST['rm_bulk_action'] );
		$status_key = 'wc-' . str_replace( 'mark_', '', $action );
		$statuses = wc_get_order_statuses();
		$status_label = isset( $statuses[ $status_key ] ) ? $statuses[ $status_key ] : $status_key;

		printf(
			'<div class="updated notice is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: number of orders, 2: status label */
					_n(
						'%1$s order status changed to %2$s.',
						'%1$s orders status changed to %2$s.',
						$count,
						'reseller-management'
					),
					number_format_i18_numeral( $count ),
					$status_label
				)
			)
		);
	}
}
