<?php
/**
 * Reseller order and customer flows.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Orders {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        add_action( 'init', [ $this, 'register_custom_order_statuses' ] );
        add_filter( 'wc_order_statuses', [ $this, 'add_custom_order_statuses' ] );
        add_action( 'wp_ajax_reseller_create_order', [ $this, 'handle_create_order' ] );
        add_action( 'wp_ajax_reseller_update_order', [ $this, 'handle_update_order' ] );
        add_action( 'wp_ajax_reseller_update_order_status', [ $this, 'handle_update_order_status' ] );
        add_action( 'wp_ajax_reseller_search_products', [ $this, 'handle_search_products' ] );
        add_action( 'template_redirect', [ $this, 'handle_print_invoice' ] );
        add_action( 'admin_init', [ $this, 'handle_admin_print_invoice' ] );
    }

    /**
     * Register custom order statuses.
     */
    public function register_custom_order_statuses() {
        register_post_status( 'wc-packaging', [
            'label'                     => __( 'Packaging', 'reseller-management' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Packaging <span class="count">(%s)</span>', 'Packaging <span class="count">(%s)</span>', 'reseller-management' ),
        ] );

        register_post_status( 'wc-shipping', [
            'label'                     => __( 'Shipping', 'reseller-management' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Shipping <span class="count">(%s)</span>', 'Shipping <span class="count">(%s)</span>', 'reseller-management' ),
        ] );

        register_post_status( 'wc-delivered', [
            'label'                     => __( 'Delivered', 'reseller-management' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'reseller-management' ),
        ] );

        register_post_status( 'wc-confirmed', [
            'label'                     => __( 'Confirmed', 'reseller-management' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', 'reseller-management' ),
        ] );

        register_post_status( 'wc-returned', [
            'label'                     => __( 'Returned', 'reseller-management' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Returned <span class="count">(%s)</span>', 'Returned <span class="count">(%s)</span>', 'reseller-management' ),
        ] );
    }

    /**
     * Add custom order statuses to WooCommerce.
     */
    public function add_custom_order_statuses( $order_statuses ) {
        $new_order_statuses = [];

        foreach ( $order_statuses as $key => $status ) {
            $new_order_statuses[ $key ] = $status;

            if ( 'wc-processing' === $key ) {
                $new_order_statuses['wc-packaging']  = __( 'Packaging', 'reseller-management' );
                $new_order_statuses['wc-shipping'] = __( 'Shipping', 'reseller-management' );
                $new_order_statuses['wc-delivered'] = __( 'Delivered', 'reseller-management' );
                $new_order_statuses['wc-confirmed'] = __( 'Confirmed', 'reseller-management' );
                $new_order_statuses['wc-returned'] = __( 'Returned', 'reseller-management' );
            }
        }

        return $new_order_statuses;
    }

    /**
     * Fetch orders assigned to a reseller.
     *
     * @param int   $user_id Reseller ID.
     * @param array $args    Optional arguments for wc_get_orders.
     *
     * @return array<int, \WC_Order>
     */
    public static function get_reseller_orders( $user_id, $args = [] ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return [];
        }

        $default_args = [
            'limit'      => -1,
            'type'       => 'shop_order',
            'orderby'    => 'date',
            'order'      => 'DESC',
            'meta_query' => [
                [
                    'key'   => '_assigned_reseller_id',
                    'value' => (string) $user_id,
                ],
            ],
        ];

        if ( ! empty( $args['search'] ) ) {
            $default_args['s'] = sanitize_text_field( $args['search'] );
        }

        if ( ! empty( $args['date_from'] ) || ! empty( $args['date_to'] ) ) {
            $date_query = [];
            if ( ! empty( $args['date_from'] ) ) {
                $date_query['after'] = $args['date_from'];
            }
            if ( ! empty( $args['date_to'] ) ) {
                $date_query['before'] = $args['date_to'];
            }
            $date_query['inclusive'] = true;
            $default_args['date_query'] = [ $date_query ];
        }

        $query_args = wp_parse_args( $args, $default_args );

        return wc_get_orders( $query_args );
    }

    /**
     * Get total order count for a reseller.
     *
     * @param int    $user_id Reseller ID.
     * @param string $status  Optional status filter.
     * @param string $search     Optional search term.
     * @param array  $args_extra Optional extra arguments (date_from, date_to).
     *
     * @return int
     */
    public static function get_reseller_order_count( $user_id, $status = '', $search = '', $args_extra = [] ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return 0;
        }

        $args = [
            'limit'      => -1,
            'return'     => 'ids',
            'meta_query' => [
                [
                    'key'   => '_assigned_reseller_id',
                    'value' => (string) $user_id,
                ],
            ],
        ];

        if ( ! empty( $status ) ) {
            $args['status'] = $status;
        }

        if ( ! empty( $search ) ) {
            $args['s'] = $search;
        }

        if ( ! empty( $args_extra['date_from'] ) || ! empty( $args_extra['date_to'] ) ) {
            $date_query = [];
            if ( ! empty( $args_extra['date_from'] ) ) {
                $date_query['after'] = $args_extra['date_from'];
            }
            if ( ! empty( $args_extra['date_to'] ) ) {
                $date_query['before'] = $args_extra['date_to'];
            }
            $date_query['inclusive'] = true;
            $args['date_query'] = [ $date_query ];
        }

        $orders = wc_get_orders( $args );
        return count( $orders );
    }

    /**
     * Get order counts by status for a reseller.
     *
     * @param int $user_id Reseller ID.
     *
     * @return array<string, int>
     */
    public static function get_order_status_counts( $user_id ) {
        $orders = self::get_reseller_orders( $user_id );
        return self::categorize_orders_for_stats( $orders );
    }

    /**
     * Get order stats for a specific number of days.
     *
     * @param int $user_id Reseller ID.
     * @param int $days    Number of days.
     *
     * @return array<string, int>
     */
    public static function get_order_stats_by_days( $user_id, $days ) {
        $args = [];
        if ( $days > 0 ) {
            $args['date_from'] = date( 'Y-m-d', strtotime( "-$days days" ) );
        } elseif ( 0 === $days ) {
            $args['date_from'] = date( 'Y-m-d 00:00:00' );
            $args['date_to']   = date( 'Y-m-d 23:59:59' );
        }

        $orders = self::get_reseller_orders( $user_id, $args );
        return self::categorize_orders_for_stats( $orders );
    }

    /**
     * Categorize orders into stats buckets.
     *
     * @param array<\WC_Order> $orders List of orders.
     *
     * @return array<string, int>
     */
    private static function categorize_orders_for_stats( $orders ) {
        $stats = [
            'completed' => 0,
            'pending'   => 0,
            'cancelled' => 0,
            'total'     => count( $orders ),
        ];

        foreach ( $orders as $order ) {
            $status = $order->get_status();
            if ( 'completed' === $status ) {
                $stats['completed']++;
            } elseif ( in_array( $status, [ 'pending', 'processing', 'on-hold' ], true ) ) {
                $stats['pending']++;
            } elseif ( in_array( $status, [ 'cancelled', 'failed', 'refunded' ], true ) ) {
                $stats['cancelled']++;
            }
        }

        return $stats;
    }

    /**
     * Build a unique customer list from reseller orders.
     *
     * @param int $user_id Reseller ID.
     *
     * @return array<int, array<string, string>>
     */
    public static function get_reseller_customers( $user_id ) {
        $customers = [];

        foreach ( self::get_reseller_orders( $user_id ) as $order ) {
            $email = (string) $order->get_billing_email();
            $key   = $email ? $email : 'order-' . $order->get_id();

            $customers[ $key ] = [
                'name'    => trim( $order->get_formatted_billing_full_name() ),
                'email'   => $email,
                'phone'   => (string) $order->get_billing_phone(),
                'city'    => (string) $order->get_billing_city(),
                'address' => (string) $order->get_billing_address_1(),
            ];
        }

        return array_values( $customers );
    }

    /**
     * Handle order creation from the dashboard.
     *
     * @return void
     */
    public function handle_create_order() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! Reseller_Helper::is_reseller_approved( get_current_user_id() ) ) {
            wp_send_json_error( __( 'You are not allowed to create orders.', 'reseller-management' ), 403 );
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            wp_send_json_error( __( 'WooCommerce is required to create orders.', 'reseller-management' ), 500 );
        }

        $customer_name    = sanitize_text_field( wp_unslash( $_POST['customer_name'] ?? '' ) );
        $customer_phone   = sanitize_text_field( wp_unslash( $_POST['customer_phone'] ?? '' ) );
        $customer_address = sanitize_textarea_field( wp_unslash( $_POST['customer_address'] ?? '' ) );
        $district         = sanitize_text_field( wp_unslash( $_POST['district'] ?? '' ) );
        $thana            = sanitize_text_field( wp_unslash( $_POST['thana'] ?? '' ) );
        $order_notes      = sanitize_textarea_field( wp_unslash( $_POST['order_notes'] ?? '' ) );
        $shipping_charge  = floatval( wp_unslash( $_POST['shipping_charge'] ?? 0 ) );
        $preset_shipping_raw = wp_unslash( $_POST['preset_shipping_charge'] ?? '' );
        $discount         = floatval( wp_unslash( $_POST['discount'] ?? 0 ) );
        $paid_amount      = floatval( wp_unslash( $_POST['paid_amount'] ?? 0 ) );
        
        $raw_items = $_POST['items'] ?? [];
        if ( is_string( $raw_items ) ) {
            $decoded_items = json_decode( wp_unslash( $raw_items ), true );
            if ( is_array( $decoded_items ) ) {
                $raw_items = $decoded_items;
            }
        }
        $items = is_array( $raw_items ) ? $raw_items : [];

        if ( '' === trim( $customer_name ) || '' === trim( $customer_phone ) || '' === trim( $customer_address ) || empty( $items ) ) {
            wp_send_json_error( __( 'Please complete all order fields.', 'reseller-management' ), 422 );
        }

        $name_parts = preg_split( '/\s+/', $customer_name );
        $first_name = $name_parts[0] ?? $customer_name;
        $last_name  = isset( $name_parts[1] ) ? implode( ' ', array_slice( $name_parts, 1 ) ) : '';

        $order = wc_create_order();
        if ( is_wp_error( $order ) ) {
            wp_send_json_error( $order->get_error_message(), 500 );
        }

        foreach ( $items as $item ) {
            $product_id = absint( $item['product_id'] ?? 0 );
            $quantity   = absint( $item['quantity'] ?? 1 );
            $resale_price = floatval( $item['resale_price'] ?? 0 );
            
            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                continue;
            }

            $order->add_product( $product, $quantity, [
                'subtotal' => $resale_price * $quantity,
                'total'    => $resale_price * $quantity,
            ] );
            
            // Store original regular price for commission calculation
            $order_items = $order->get_items();
            $last_item = end($order_items);
            if ($last_item) {
                $last_item->add_meta_data('_resale_price', $resale_price);
                $last_item->add_meta_data('_base_price', $product->get_price());
                $last_item->save();
            }
        }

        if ( ! count( $order->get_items() ) ) {
            $order->delete( true );
            wp_send_json_error( __( 'No valid products were selected.', 'reseller-management' ), 422 );
        }

        $order->set_address(
            [
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'phone'      => $customer_phone,
                'address_1'  => $customer_address,
                'address_2'  => $thana,
                'city'       => $district,
            ],
            'billing'
        );

        $order->set_address(
            [
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'phone'      => $customer_phone,
                'address_1'  => $customer_address,
                'address_2'  => $thana,
                'city'       => $district,
            ],
            'shipping'
        );

        if ( $order_notes ) {
            $order->set_customer_note( $order_notes );
        }

        // Apply shipping charge
        if ( $shipping_charge > 0 ) {
            $shipping_item = new \WC_Order_Item_Shipping();
            $shipping_item->set_method_title( __( 'Shipping', 'reseller-management' ) );
            $shipping_item->set_total( $shipping_charge );
            $order->add_item( $shipping_item );
        }

        // Apply discount
        if ( $discount > 0 ) {
            $order->set_discount_total( $discount );
        }

        // Handle advance payment deduction from balance.
        if ( $paid_amount > 0 ) {
            $user_id         = get_current_user_id();
            $current_balance = Reseller_Helper::get_current_balance( $user_id );

            if ( $paid_amount > $current_balance ) {
                $order->delete( true );
                wp_send_json_error( __( 'Insufficient balance for advance payment.', 'reseller-management' ), 422 );
            }

            Reseller_Helper::insert_ledger_entry(
                [
                    'reseller_id' => $user_id,
                    'order_id'    => $order->get_id(),
                    'type'        => 'advance_payment',
                    'amount'      => -1 * abs( $paid_amount ),
                    'description' => sprintf( 'Advance payment for Order #%d', $order->get_id() ),
                ]
            );

            // Apply advance payment as a negative fee to reduce the Order Total
            $fee = new \WC_Order_Item_Fee();
            $fee->set_name( __( 'Advance Paid', 'reseller-management' ) );
            $fee->set_amount( -$paid_amount );
            $fee->set_total( -$paid_amount );
            // Ensure no tax is calculated on this fee
            $fee->set_tax_class( '' );
            $fee->set_tax_status( 'none' );
            $order->add_item( $fee );
        }

        $order->update_meta_data( '_assigned_reseller_id', get_current_user_id() );
        $order->update_meta_data( '_order_district', $district );
        $order->update_meta_data( '_order_thana', $thana );
        $order->update_meta_data( '_paid_amount', $paid_amount );
        if ( '' !== (string) $preset_shipping_raw && is_numeric( $preset_shipping_raw ) ) {
            $order->update_meta_data( '_shipping_base_charge', max( 0.0, round( (float) $preset_shipping_raw, 2 ) ) );
        }
        
        $order->calculate_totals();
        $order->set_status( 'processing' );
        $order->save();

        if ( ob_get_length() ) {
            ob_clean();
        }
        wp_send_json_success(
            sprintf(
                /* translators: %d: order ID. */
                __( 'Order #%d created successfully.', 'reseller-management' ),
                $order->get_id()
            )
        );
    }
    
    /**
     * Handle updating an existing order.
     */
    public function handle_update_order() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( __( 'Unauthorized.', 'reseller-management' ), 403 );
        }

        $user_id = get_current_user_id();
        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        $order    = wc_get_order( $order_id );

        if ( ! $order || (int) $order->get_meta( '_assigned_reseller_id' ) !== (int) $user_id ) {
            wp_send_json_error( __( 'Order not found or access denied.', 'reseller-management' ), 404 );
        }

        // Extract and sanitize data
        $customer_name    = sanitize_text_field( wp_unslash( $_POST['customer_name'] ?? '' ) );
        $customer_phone   = sanitize_text_field( wp_unslash( $_POST['customer_phone'] ?? '' ) );
        $customer_address = sanitize_textarea_field( wp_unslash( $_POST['customer_address'] ?? '' ) );
        $district         = sanitize_text_field( wp_unslash( $_POST['district'] ?? '' ) );
        $thana            = sanitize_text_field( wp_unslash( $_POST['thana'] ?? '' ) );
        $order_notes      = sanitize_textarea_field( wp_unslash( $_POST['order_notes'] ?? '' ) );
        $shipping_charge  = floatval( wp_unslash( $_POST['shipping_charge'] ?? 0 ) );
        $preset_shipping_raw = wp_unslash( $_POST['preset_shipping_charge'] ?? '' );
        $discount         = floatval( wp_unslash( $_POST['discount'] ?? 0 ) );
        $paid_amount      = floatval( wp_unslash( $_POST['paid_amount'] ?? 0 ) );
        $raw_items = $_POST['items'] ?? [];
        if ( is_string( $raw_items ) ) {
            $decoded_items = json_decode( wp_unslash( $raw_items ), true );
            if ( is_array( $decoded_items ) ) {
                $raw_items = $decoded_items;
            }
        }
        $items = is_array( $raw_items ) ? $raw_items : [];

        if ( '' === trim( $customer_name ) || '' === trim( $customer_phone ) || '' === trim( $customer_address ) || empty( $items ) ) {
            wp_send_json_error( __( 'Please complete all order fields.', 'reseller-management' ), 422 );
        }

        $name_parts = preg_split( '/\s+/', $customer_name );
        $first_name = $name_parts[0] ?? $customer_name;
        $last_name  = isset( $name_parts[1] ) ? implode( ' ', array_slice( $name_parts, 1 ) ) : '';

        // Clear existing items and shipping
        $order->remove_order_items();

        // Add new items
        foreach ( $items as $item ) {
            $product_id   = absint( $item['product_id'] ?? 0 );
            $quantity     = absint( $item['quantity'] ?? 1 );
            $resale_price = floatval( $item['resale_price'] ?? 0 );
            
            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                continue;
            }

            $order->add_product( $product, $quantity, [
                'subtotal' => $resale_price * $quantity,
                'total'    => $resale_price * $quantity,
            ] );
            
            foreach ( $order->get_items() as $added_item ) {
                if ( (int) $added_item->get_product_id() === $product_id ) {
                    $added_item->add_meta_data('_resale_price', $resale_price, true);
                    $added_item->add_meta_data('_base_price', $product->get_price(), true);
                    $added_item->save();
                    break;
                }
            }
        }

        $order->set_address( [
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'phone'      => $customer_phone,
            'address_1'  => $customer_address,
            'address_2'  => $thana,
            'city'       => $district,
        ], 'billing' );

        $order->set_address( [
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'phone'      => $customer_phone,
            'address_1'  => $customer_address,
            'address_2'  => $thana,
            'city'       => $district,
        ], 'shipping' );

        if ( $order_notes ) {
            $order->set_customer_note( $order_notes );
        }

        if ( $shipping_charge > 0 ) {
            $shipping_item = new \WC_Order_Item_Shipping();
            $shipping_item->set_method_title( __( 'Shipping', 'reseller-management' ) );
            $shipping_item->set_total( $shipping_charge );
            $order->add_item( $shipping_item );
        }

        $order->set_discount_total( $discount );
        $order->update_meta_data( '_order_district', $district );
        $order->update_meta_data( '_order_thana', $thana );
        if ( '' !== (string) $preset_shipping_raw && is_numeric( $preset_shipping_raw ) ) {
            $order->update_meta_data( '_shipping_base_charge', max( 0.0, round( (float) $preset_shipping_raw, 2 ) ) );
        }

        // Handle advance payment adjustment.
        $old_paid_amount = (float) $order->get_meta( '_paid_amount' );
        if ( $paid_amount !== $old_paid_amount ) {
            $diff = $paid_amount - $old_paid_amount;

            if ( $diff > 0 ) {
                // Additional deduction check
                $current_balance = Reseller_Helper::get_current_balance( $user_id );
                if ( $diff > $current_balance ) {
                    wp_send_json_error( __( 'Insufficient balance for additional advance payment.', 'reseller-management' ), 422 );
                }
            }

            Reseller_Helper::insert_ledger_entry(
                [
                    'reseller_id' => $user_id,
                    'order_id'    => $order->get_id(),
                    'type'        => 'advance_payment_adjustment',
                    'amount'      => -1 * $diff,
                    'description' => sprintf( 'Advance payment adjustment for Order #%d', $order->get_id() ),
                ]
            );

            $order->update_meta_data( '_paid_amount', $paid_amount );
        }
        
        // Always remove existing advance paid fee to re-apply correctly
        foreach ( $order->get_items( 'fee' ) as $item_id => $item ) {
            if ( $item->get_name() === __( 'Advance Paid', 'reseller-management' ) ) {
                $order->remove_item( $item_id );
            }
        }

        // Apply new advance payment as a negative fee
        if ( $paid_amount > 0 ) {
            $fee = new \WC_Order_Item_Fee();
            $fee->set_name( __( 'Advance Paid', 'reseller-management' ) );
            $fee->set_amount( -$paid_amount );
            $fee->set_total( -$paid_amount );
            $fee->set_tax_class( '' );
            $fee->set_tax_status( 'none' );
            $order->add_item( $fee );
        }

        $order->calculate_totals();
        $order->save();

        wp_send_json_success( __( 'Order updated successfully.', 'reseller-management' ) );
    }

    /**
     * Update order status.
 updates from the dashboard.
     *
     * @return void
     */
    public function handle_update_order_status() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! is_user_logged_in() || ! Reseller_Helper::is_reseller_approved( $user_id ) ) {
            wp_send_json_error( __( 'You are not allowed to update orders.', 'reseller-management' ), 403 );
        }

        $order_id = absint( $_POST['order_id'] ?? 0 );
        $new_status = sanitize_key( $_POST['status'] ?? '' );
        
        if ( ! $order_id || ! $new_status ) {
            wp_send_json_error( __( 'Invalid request parameters.', 'reseller-management' ), 422 );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( __( 'Order not found.', 'reseller-management' ), 404 );
        }

        // Verify order ownership
        if ( (int) $order->get_meta( '_assigned_reseller_id' ) !== $user_id ) {
            wp_send_json_error( __( 'You do not have permission to update this order.', 'reseller-management' ), 403 );
        }

        // Validate status
        $valid_statuses = [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'packaging', 'shipping', 'delivered', 'confirmed' ];
        if ( ! in_array( $new_status, $valid_statuses, true ) ) {
            wp_send_json_error( __( 'Invalid status value.', 'reseller-management' ), 422 );
        }

        $order->update_status( $new_status );
        
        wp_send_json_success( __( 'Order status updated successfully.', 'reseller-management' ) );
    }

    /**
     * Handle product search for order creation.
     */
    public function handle_search_products() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        $query = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
        if ( strlen( $query ) < 2 ) {
            wp_send_json_success( [] );
        }

        $args = [
            'limit'   => 10,
            'status'  => 'publish',
            's'       => $query,
        ];

        // Check if query is also a SKU or Barcode (metakey might vary, assuming _sku)
        $products = wc_get_products( $args );

        // If no products found by name, try SKU
        if ( empty( $products ) ) {
            $args = [
                'limit'  => 10,
                'status' => 'publish',
                'sku'    => $query,
            ];
            $products = wc_get_products( $args );
        }

        $results = [];
        foreach ( $products as $product ) {
            $recommended_price = $product->get_meta( '_reseller_recommended_price' );
            if ( empty( $recommended_price ) ) {
                $recommended_price = $product->get_price();
            }

            $results[] = [
                'id'                => $product->get_id(),
                'text'              => $product->get_name(),
                'price'             => $product->get_price(),
                'recommended_price' => $recommended_price,
                'image'             => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ),
                'sku'               => $product->get_sku(),
                'variants'          => $this->get_product_variants( $product ),
            ];
        }

        wp_send_json_success( $results );
    }

    /**
     * Get variants for a product.
     */
    private function get_product_variants( $product ) {
        if ( ! $product->is_type( 'variable' ) ) {
            return [];
        }

        $variants = [];
        foreach ( $product->get_available_variations() as $variation_data ) {
            $variation_id = $variation_data['variation_id'];
            $variation    = wc_get_product( $variation_id );
            
            $recommended_price = $variation->get_meta( '_reseller_recommended_price' );
            if ( empty( $recommended_price ) ) {
                $recommended_price = $variation->get_price();
            }

            $variants[] = [
                'id'                => $variation_id,
                'attributes'        => $variation_data['attributes'],
                'price'             => $variation_data['display_price'],
                'recommended_price' => $recommended_price,
            ];
        }

        return $variants;
    }

    /**
     * Handle printing of the order invoice.
     *
     * @return void
     */
    public function handle_print_invoice() {
        if ( ! isset( $_GET['rm_action'] ) || 'print_invoice' !== $_GET['rm_action'] ) {
            return;
        }

        $order_id = absint( $_GET['order_id'] ?? 0 );
        if ( ! $order_id || ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'rm_public_nonce' ) ) {
            wp_die( esc_html__( 'Invalid request or link expired.', 'reseller-management' ) );
        }

        if ( ! is_user_logged_in() || ! Reseller_Helper::is_reseller_approved( get_current_user_id() ) ) {
            wp_die( esc_html__( 'Unauthorized.', 'reseller-management' ) );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || (int) $order->get_meta( '_assigned_reseller_id' ) !== get_current_user_id() ) {
            wp_die( esc_html__( 'Order not found or access denied.', 'reseller-management' ) );
        }

        $template = PLUGIN_BASE_PATH . '/templates/dashboard/invoice.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            wp_die( esc_html__( 'Invoice template not found.', 'reseller-management' ) );
        }
        exit;
    }

    /**
     * Admin: full-page invoice view (same template as reseller dashboard) with print control.
     *
     * @return void
     */
    public function handle_admin_print_invoice() {
        if ( ! isset( $_GET['rm_action'] ) || 'admin_print_invoice' !== $_GET['rm_action'] ) {
            return;
        }

        if ( ! is_admin() || ! is_user_logged_in() ) {
            wp_die( esc_html__( 'Unauthorized.', 'reseller-management' ) );
        }

        $order_id = absint( $_GET['order_id'] ?? 0 );
        if ( ! $order_id || ! isset( $_GET['nonce'] ) ) {
            wp_die( esc_html__( 'Invalid request or link expired.', 'reseller-management' ) );
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'rm_admin_print_invoice_' . $order_id ) ) {
            wp_die( esc_html__( 'Invalid request or link expired.', 'reseller-management' ) );
        }

        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            wp_die( esc_html__( 'You do not have permission to print this invoice.', 'reseller-management' ) );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_die( esc_html__( 'Order not found.', 'reseller-management' ) );
        }

        $template = PLUGIN_BASE_PATH . '/templates/dashboard/invoice.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            wp_die( esc_html__( 'Invoice template not found.', 'reseller-management' ) );
        }
        exit;
    }
}
