<?php
/**
 * Steadfast Webhook Listener
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;
use WP_REST_Request;
use WP_REST_Response;

class Steadfast_Webhook {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_webhook_route' ] );
    }

    /**
     * Register the REST API route for the webhook.
     */
    public function register_webhook_route() {
        register_rest_route( 'reseller/v1', '/steadfast-webhook', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle_webhook_request' ],
            'permission_callback' => '__return_true', // Validation happens inside the callback
        ] );
    }

    /**
     * Handle the incoming webhook request.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response
     */
    public function handle_webhook_request( WP_REST_Request $request ) {
        // Verify Global Secret Token
        $rm_settings  = get_option( 'rm_settings', [] );
        $saved_token  = $rm_settings['steadfast_secret_token'] ?? '';
        $auth_header  = $request->get_header( 'Authorization' );
        
        // Steadfast sends "Bearer [token]"
        $provided_token = str_replace( 'Bearer ', '', (string) $auth_header );

        if ( empty( $saved_token ) || $provided_token !== $saved_token ) {
            return new WP_REST_Response( [ 'status' => 'error', 'message' => 'Unauthorized' ], 401 );
        }

        $payload = $request->get_json_params();

        if ( empty( $payload ) ) {
            return new WP_REST_Response( [ 'status' => 'error', 'message' => 'Empty payload' ], 400 );
        }

        $notification_type = $payload['notification_type'] ?? '';
        $invoice           = $payload['invoice'] ?? '';
        $consignment_id    = $payload['consignment_id'] ?? '';
        $status            = $payload['status'] ?? '';
        $tracking_message  = $payload['tracking_message'] ?? '';

        if ( empty( $invoice ) ) {
            return new WP_REST_Response( [ 'status' => 'error', 'message' => 'Missing invoice (Order ID)' ], 400 );
        }

        // Find the order
        $order_id = str_replace( 'M', '', $invoice );
        $order    = wc_get_order( (int) $order_id );

        if ( ! $order ) {
            return new WP_REST_Response( [ 'status' => 'error', 'message' => 'Order not found' ], 404 );
        }

        // Verify that it is a reseller order
        if ( ! $order->get_meta( '_assigned_reseller_id' ) ) {
            return new WP_REST_Response( [ 'status' => 'error', 'message' => 'Not a reseller order' ], 403 );
        }

        // Update tracking info
        if ( ! empty( $consignment_id ) ) {
            $order->update_meta_data( '_steadfast_consignment_id', $consignment_id );
        }
        if ( ! empty( $tracking_message ) ) {
            $order->update_meta_data( '_steadfast_tracking_message', $tracking_message );
        }

        // Handle Delivery Status Update
        if ( 'delivery_status_update' === $notification_type && ! empty( $status ) ) {
            $this->update_order_status( $order, $status );
        }

        $order->save();

        return new WP_REST_Response( [ 'status' => 'success', 'message' => 'Webhook received successfully.' ], 200 );
    }

    /**
     * Map Steadfast status to WooCommerce status and update order.
     *
     * @param \WC_Order $order  The order object.
     * @param string    $status Steadfast status.
     */
    private function update_order_status( $order, $status ) {
        $status_map = [
            'pending'           => 'processing',
            'delivered'         => 'completed',
            'partial_delivered' => 'completed',
            'cancelled'         => 'cancelled',
            'unknown'           => 'on-hold',
        ];

        $new_wc_status = $status_map[ $status ] ?? '';

        if ( $new_wc_status ) {
            $order->update_status( $new_wc_status, sprintf( __( 'Status updated via Steadfast Webhook: %s', 'reseller-management' ), $status ) );
        }
    }
}
