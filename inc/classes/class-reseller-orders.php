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
        add_action( 'wp_ajax_reseller_create_order', [ $this, 'handle_create_order' ] );
        add_action( 'wp_ajax_reseller_update_order', [ $this, 'handle_update_order' ] );
        add_action( 'wp_ajax_reseller_update_order_status', [ $this, 'handle_update_order_status' ] );
        add_action( 'wp_ajax_reseller_search_products', [ $this, 'handle_search_products' ] );
    }

    /**
     * Fetch orders assigned to a reseller.
     *
     * @param int $user_id Reseller ID.
     *
     * @return array<int, \WC_Order>
     */
    public static function get_reseller_orders( $user_id ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return [];
        }

        return wc_get_orders(
            [
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
            ]
        );
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
        $counts = [
            'new'        => 0,
            'pending'    => 0,
            'confirmed'  => 0,
            'packaging'  => 0,
            'shipment'   => 0,
            'delivered'  => 0,
            'wfr'        => 0,
            'returned'   => 0,
            'cancel'     => 0,
            'all'        => count( $orders ),
            'incomplete' => 0,
        ];

        foreach ( $orders as $order ) {
            $status = $order->get_status();
            
            // Map WC statuses to our display categories
            // Note: These mappings might need adjustment based on custom statuses in use.
            switch ( $status ) {
                case 'pending':
                    $counts['pending']++;
                    break;
                case 'processing':
                    $counts['new']++;
                    break;
                case 'on-hold':
                    $counts['confirmed']++;
                    break;
                case 'completed':
                    $counts['delivered']++;
                    break;
                case 'cancelled':
                    $counts['cancel']++;
                    break;
                case 'refunded':
                    $counts['returned']++;
                    break;
                case 'failed':
                    $counts['incomplete']++;
                    break;
                // Add more cases for custom statuses if available
            }
        }

        return $counts;
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
                'name'  => trim( $order->get_formatted_billing_full_name() ),
                'email' => $email,
                'phone' => (string) $order->get_billing_phone(),
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
        $discount         = floatval( wp_unslash( $_POST['discount'] ?? 0 ) );
        $paid_amount      = floatval( wp_unslash( $_POST['paid_amount'] ?? 0 ) );
        
        $items = isset( $_POST['items'] ) && is_array( $_POST['items'] ) ? $_POST['items'] : [];

        if ( empty( $customer_name ) || empty( $customer_phone ) || empty( $customer_address ) || empty( $items ) ) {
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

        $order->update_meta_data( '_assigned_reseller_id', get_current_user_id() );
        $order->update_meta_data( '_order_district', $district );
        $order->update_meta_data( '_order_thana', $thana );
        $order->update_meta_data( '_paid_amount', $paid_amount );
        
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
        $discount         = floatval( wp_unslash( $_POST['discount'] ?? 0 ) );
        $paid_amount      = floatval( wp_unslash( $_POST['paid_amount'] ?? 0 ) );
        $items            = isset( $_POST['items'] ) && is_array( $_POST['items'] ) ? $_POST['items'] : [];

        if ( empty( $customer_name ) || empty( $customer_phone ) || empty( $customer_address ) || empty( $items ) ) {
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
        $order->update_meta_data( '_paid_amount', $paid_amount );
        
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
        $valid_statuses = [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' ];
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
}
