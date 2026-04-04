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
        $product_ids      = array_filter( array_map( 'absint', (array) ( $_POST['product_ids'] ?? [] ) ) );

        if ( empty( $customer_name ) || empty( $customer_phone ) || empty( $customer_address ) || empty( $product_ids ) ) {
            wp_send_json_error( __( 'Please complete all order fields.', 'reseller-management' ), 422 );
        }

        $name_parts = preg_split( '/\s+/', $customer_name );
        $first_name = $name_parts[0] ?? $customer_name;
        $last_name  = isset( $name_parts[1] ) ? implode( ' ', array_slice( $name_parts, 1 ) ) : '';

        $order = wc_create_order();
        if ( is_wp_error( $order ) ) {
            wp_send_json_error( $order->get_error_message(), 500 );
        }

        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                continue;
            }

            $order->add_product( $product, 1 );
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
            ],
            'billing'
        );

        $order->set_address(
            [
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'phone'      => $customer_phone,
                'address_1'  => $customer_address,
            ],
            'shipping'
        );

        $order->update_meta_data( '_assigned_reseller_id', get_current_user_id() );
        $order->calculate_totals();
        $order->set_status( 'processing' );
        $order->save();

        wp_send_json_success(
            sprintf(
                /* translators: %d: order ID. */
                __( 'Order #%d created successfully.', 'reseller-management' ),
                $order->get_id()
            )
        );
    }
}
