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
		add_filter( 'manage_edit-shop_order_columns', [ $this, 'add_tracking_link_column' ], 26 );
		add_action( 'manage_shop_order_custom_column', [ $this, 'render_tracking_link_column' ], 26, 2 );

		// High-Performance Order Storage (HPOS)
		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'add_reseller_column' ], 20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'render_reseller_column_hpos' ], 20, 2 );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'add_print_invoice_column' ], 25 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'render_print_invoice_column_hpos' ], 25, 2 );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'add_tracking_link_column' ], 26 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'render_tracking_link_column_hpos' ], 26, 2 );
		
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
		add_action( 'admin_init', [ $this, 'maybe_save_order_tracking_link' ] );
		add_action( 'admin_post_rm_save_order_tracking_link', [ $this, 'handle_save_order_tracking_link' ] );
		add_action( 'wp_ajax_rm_save_order_tracking', [ $this, 'ajax_save_order_tracking' ] );
		add_action( 'admin_footer', [ $this, 'print_order_tracking_save_script' ], 99 );
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
	 * Add "Tracking Link" column for order list (HPOS + legacy lists).
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_tracking_link_column( $columns ) {
		if ( isset( $columns['rm_tracking_link'] ) ) {
			return $columns;
		}

		$new_columns = [];

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			if ( 'rm_print_invoice' === $key ) {
				$new_columns['rm_tracking_link'] = __( 'Tracking Link', 'reseller-management' );
			}
		}

		if ( ! isset( $new_columns['rm_tracking_link'] ) ) {
			$new_columns['rm_tracking_link'] = __( 'Tracking Link', 'reseller-management' );
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
	 * Render tracking link column (Traditional orders).
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_tracking_link_column( $column, $post_id ) {
		if ( 'rm_tracking_link' !== $column ) {
			return;
		}

		$order = wc_get_order( $post_id );
		if ( ! $order ) {
			echo '&mdash;';
			return;
		}

		$this->render_tracking_link_form( $order );
	}

	/**
	 * Render tracking link column (HPOS).
	 *
	 * @param string    $column Column key.
	 * @param \WC_Order $order  Order object.
	 * @return void
	 */
	public function render_tracking_link_column_hpos( $column, $order ) {
		if ( 'rm_tracking_link' !== $column ) {
			return;
		}

		if ( ! $order instanceof \WC_Order ) {
			echo '&mdash;';
			return;
		}

		$this->render_tracking_link_form( $order );
	}

	/**
	 * Render tracking link update form in admin order list.
	 *
	 * @param \WC_Order $order Order object.
	 * @return void
	 */
	private function render_tracking_link_form( $order ) {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			echo '&mdash;';
			return;
		}

		$order_id      = $order->get_id();
		$tracking_link = (string) $order->get_meta( '_rm_tracking_link', true );
		$nonce         = wp_create_nonce( 'rm_save_order_tracking_link_' . $order_id );

		printf(
			'<div class="rm-tracking-row">
				<input type="url" value="%3$s" class="rm-tracking-link-input regular-text" placeholder="%4$s" autocomplete="off" />
				<div class="rm-tracking-actions">
					<button type="button" class="button button-small rm-save-tracking-btn" data-order-id="%1$d" data-nonce="%2$s">%5$s</button>
					<span class="rm-tracking-save-message" aria-live="polite"></span>
				</div>
			</div>',
			(int) $order_id,
			esc_attr( $nonce ),
			esc_attr( $tracking_link ),
			esc_attr__( 'https://tracking.example/abc', 'reseller-management' ),
			esc_html__( 'Save', 'reseller-management' )
		);
	}

	/**
	 * Inline script: POST tracking link via admin-ajax (WC orders list table uses a GET form — nested submit never reached POST handler).
	 *
	 * @return void
	 */
	public function print_order_tracking_save_script() {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}
		$allowed = false;
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && in_array( $screen->id, [ 'woocommerce_page_wc-orders', 'edit-shop_order' ], true ) ) {
				$allowed = true;
			}
		}
		if ( ! $allowed && isset( $_GET['page'] ) && 'wc-orders' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			$allowed = true;
		}
		if ( ! $allowed && isset( $_GET['post_type'] ) && 'shop_order' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) {
			$allowed = true;
		}
		if ( ! $allowed ) {
			return;
		}
		$url = admin_url( 'admin-ajax.php' );
		$error_message = __( 'Could not save tracking link. Please try again.', 'reseller-management' );
		$saving_text   = __( 'Saving...', 'reseller-management' );
		$saved_text    = __( 'Saved', 'reseller-management' );
		$failed_text   = __( 'Failed', 'reseller-management' );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<style>
			th.column-rm_tracking_link, td.column-rm_tracking_link { width: 260px; min-width: 220px; }
			.rm-tracking-row { display: flex; flex-direction: column; gap: 6px; min-width: 0; max-width: 100%; }
			.rm-tracking-row .rm-tracking-link-input { width: 100%; min-width: 0; max-width: 100%; box-sizing: border-box; }
			.rm-tracking-row .rm-tracking-actions { display: flex; align-items: center; gap: 6px; }
			.rm-tracking-row .rm-tracking-save-message { font-size: 11px; color: #50575e; }
			.rm-tracking-row .rm-tracking-save-message.is-success { color: #0a7f2e; }
			.rm-tracking-row .rm-tracking-save-message.is-error { color: #b32d2e; }
		</style>' . "\n";
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<script>(function(){var u=' . wp_json_encode( $url ) . ',saving=' . wp_json_encode( $saving_text ) . ',saved=' . wp_json_encode( $saved_text ) . ',failed=' . wp_json_encode( $failed_text ) . ',fallbackErr=' . wp_json_encode( $error_message ) . ";function setStatus(el,msg,type){if(!el)return;el.textContent=msg||'';el.classList.remove('is-success','is-error');if(type){el.classList.add(type==='success'?'is-success':'is-error');}}function isTrackingTarget(t){return !!(t&&t.closest&&t.closest('.rm-tracking-row'));}document.addEventListener('mousedown',function(e){if(isTrackingTarget(e.target)){e.stopPropagation();}},true);document.addEventListener('dblclick',function(e){if(isTrackingTarget(e.target)){e.stopPropagation();}},true);document.addEventListener('click',function(e){var b=e.target.closest('.rm-save-tracking-btn');if(b){e.preventDefault();e.stopPropagation();var w=b.closest('.rm-tracking-row'),inp=w?w.querySelector('.rm-tracking-link-input'):null,msg=w?w.querySelector('.rm-tracking-save-message'):null,id=b.getAttribute('data-order-id'),n=b.getAttribute('data-nonce');if(!inp||!id)return;var before=b.textContent;b.disabled=true;b.textContent=saving;setStatus(msg,'');var fd=new FormData();fd.append('action','rm_save_order_tracking');fd.append('nonce',n||'');fd.append('order_id',id);fd.append('tracking_link',(inp.value||'').trim());fetch(u,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){var ct=r.headers.get('content-type')||'';if(ct.indexOf('application/json')===-1){return r.text().then(function(){throw new Error(fallbackErr);});}return r.json();}).then(function(d){b.disabled=false;b.textContent=before;if(d&&d.success){if(d.data&&typeof d.data.tracking_link==='string'){inp.value=d.data.tracking_link;}setStatus(msg,saved,'success');window.setTimeout(function(){setStatus(msg,'');},2500);return;}var m=d&&d.data&&d.data.message?d.data.message:fallbackErr;setStatus(msg,failed,'error');window.alert(m);}).catch(function(err){b.disabled=false;b.textContent=before;setStatus(msg,failed,'error');window.alert((err&&err.message)?err.message:fallbackErr);});return;}if(isTrackingTarget(e.target)){e.stopPropagation();}},true);})();</script>\n";
	}

	/**
	 * AJAX: save order tracking link (orders list screen).
	 *
	 * @return void
	 */
	public function ajax_save_order_tracking() {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_send_json_error( [ 'message' => __( 'You do not have permission to edit shop orders.', 'reseller-management' ) ], 403 );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : 0;
		$nonce    = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! $order_id || ! wp_verify_nonce( $nonce, 'rm_save_order_tracking_link_' . $order_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid request.', 'reseller-management' ) ], 400 );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_send_json_error( [ 'message' => __( 'Order not found.', 'reseller-management' ) ], 404 );
		}

		$tracking_input = isset( $_POST['tracking_link'] ) ? wp_unslash( $_POST['tracking_link'] ) : '';
		$tracking_link  = $this->sanitize_tracking_link( $tracking_input );

		if ( '' === $tracking_link ) {
			$order->delete_meta_data( '_rm_tracking_link' );
		} else {
			$order->update_meta_data( '_rm_tracking_link', $tracking_link );
		}
		$order->save();

		wp_send_json_success(
			[
				'message'       => __( 'Tracking link saved.', 'reseller-management' ),
				'tracking_link' => $tracking_link,
			]
		);
	}

	/**
	 * Save tracking link when submitted from orders list parent form.
	 *
	 * @return void
	 */
	public function maybe_save_order_tracking_link() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || empty( $_POST['rm_save_tracking_order_id'] ) ) {
			return;
		}

		$this->handle_save_order_tracking_link();
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
		$actions['reseller_print_invoices'] = __( 'Print Invoices', 'reseller-management' );

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
		if ( 'reseller_print_invoices' === $action ) {
			return add_query_arg(
				[
					'rm_action' => 'admin_bulk_print_invoice',
					'order_ids' => implode( ',', array_map( 'absint', $ids ) ),
					'nonce'     => wp_create_nonce( 'rm_admin_bulk_print' ),
				],
				admin_url( 'admin.php' )
			);
		}

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

	/**
	 * Save tracking link from admin order list.
	 *
	 * @return void
	 */
	public function handle_save_order_tracking_link() {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( esc_html__( 'You do not have permission to edit shop orders.', 'reseller-management' ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : 0;
		if ( ! $order_id && isset( $_POST['rm_save_tracking_order_id'] ) ) {
			$order_id = absint( wp_unslash( $_POST['rm_save_tracking_order_id'] ) );
		}

		$nonce = '';
		if ( isset( $_POST['rm_tracking_nonce'] ) && is_array( $_POST['rm_tracking_nonce'] ) && $order_id ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['rm_tracking_nonce'][ $order_id ] ?? '' ) );
		} elseif ( isset( $_POST['rm_tracking_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['rm_tracking_nonce'] ) );
		}

		if ( ! $order_id || ! wp_verify_nonce( $nonce, 'rm_save_order_tracking_link_' . $order_id ) ) {
			wp_die( esc_html__( 'Invalid request.', 'reseller-management' ) );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_die( esc_html__( 'Order not found.', 'reseller-management' ) );
		}

		$tracking_input = '';
		if ( isset( $_POST['tracking_link'] ) && is_array( $_POST['tracking_link'] ) && $order_id ) {
			$tracking_input = wp_unslash( $_POST['tracking_link'][ $order_id ] ?? '' );
		} elseif ( isset( $_POST['tracking_link'] ) ) {
			$tracking_input = wp_unslash( $_POST['tracking_link'] );
		}
		$tracking_link = $this->sanitize_tracking_link( $tracking_input );

		if ( empty( $tracking_link ) ) {
			$order->delete_meta_data( '_rm_tracking_link' );
		} else {
			$order->update_meta_data( '_rm_tracking_link', $tracking_link );
		}

		$order->save();

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = admin_url( 'admin.php?page=wc-orders' );
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Sanitize and normalize tracking link before saving.
	 *
	 * @param string $tracking_input Raw tracking input.
	 * @return string
	 */
	private function sanitize_tracking_link( $tracking_input ) {
		$tracking_input = trim( (string) $tracking_input );

		if ( '' === $tracking_input ) {
			return '';
		}

		// Allow users to paste domains without a scheme.
		if ( ! preg_match( '#^[a-z][a-z0-9+\-.]*://#i', $tracking_input ) ) {
			$tracking_input = 'https://' . ltrim( $tracking_input, '/' );
		}

		return esc_url_raw( $tracking_input, [ 'http', 'https' ] );
	}
}
