<?php
/**
 * Ledger and withdrawal flows.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Finance {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        add_action( 'woocommerce_order_status_completed', [ $this, 'credit_order_commission' ] );
        add_action( 'woocommerce_order_status_refunded', [ $this, 'debit_order_shipping' ] );
        add_action( 'wp_ajax_reseller_request_withdrawal', [ $this, 'handle_withdrawal_request' ] );
    }

    /**
     * Check whether the order already has a matching ledger record.
     *
     * @param int    $order_id Order ID.
     * @param string $type     Ledger type.
     *
     * @return bool
     */
    public static function ledger_entry_exists_for_order( $order_id, $type ) {
        global $wpdb;

        $table = Reseller_Helper::get_ledger_table_name();
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(id) FROM {$table} WHERE order_id = %d AND type = %s",
                $order_id,
                $type
            )
        );

        return (int) $count > 0;
    }

    /**
     * Get ledger rows for a reseller.
     *
     * @param int $user_id Reseller ID.
     *
     * @return array<int, object>
     */
    public static function get_transactions( $user_id ) {
        global $wpdb;

        $table = Reseller_Helper::get_ledger_table_name();

        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE reseller_id = %d ORDER BY created_at DESC, id DESC",
                $user_id
            )
        );
    }

    /**
     * Get reseller withdrawals for admin or frontend.
     *
     * @param int|null $user_id Optional reseller ID.
     *
     * @return array<int, object>
     */
    public static function get_withdrawals( $user_id = null ) {
        global $wpdb;

        $table = Reseller_Helper::get_withdrawals_table_name();
        if ( null === $user_id ) {
            return (array) $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC, id DESC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE reseller_id = %d ORDER BY created_at DESC, id DESC",
                $user_id
            )
        );
    }

    /**
     * Calculate commission for an order.
     *
     * @param \WC_Order $order Order object.
     *
     * @return float
     */
    public static function get_order_commission_total( $order ) {
        $total = 0;

        foreach ( $order->get_items() as $item ) {
            $resale_price = (float) $item->get_meta( '_resale_price', true );
            $base_price   = (float) $item->get_meta( '_base_price', true );
            $quantity     = (int) $item->get_quantity();

            if ( $resale_price > 0 && $base_price > 0 ) {
                $commission = $resale_price - $base_price;
                $total     += $commission * max( $quantity, 1 );
            }
        }

        return round( $total, 2 );
    }

    /**
     * Credit commission when the order is completed.
     *
     * @param int $order_id Order ID.
     *
     * @return void
     */
    public function credit_order_commission( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $reseller_id = (int) $order->get_meta( '_assigned_reseller_id', true );
        if ( ! $reseller_id || self::ledger_entry_exists_for_order( $order_id, 'commission_credit' ) ) {
            return;
        }

        $commission = self::get_order_commission_total( $order );
        if ( $commission <= 0 ) {
            return;
        }

        Reseller_Helper::insert_ledger_entry(
            [
                'reseller_id' => $reseller_id,
                'order_id'    => $order_id,
                'type'        => 'commission_credit',
                'amount'      => $commission,
                'description' => sprintf( 'Commission for Order #%d', $order_id ),
            ]
        );
    }

    /**
     * Debit shipping on refunded orders.
     *
     * @param int $order_id Order ID.
     *
     * @return void
     */
    public function debit_order_shipping( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $reseller_id = (int) $order->get_meta( '_assigned_reseller_id', true );
        if ( ! $reseller_id || self::ledger_entry_exists_for_order( $order_id, 'shipping_debit' ) ) {
            return;
        }

        $shipping_total = (float) $order->get_shipping_total();
        if ( $shipping_total <= 0 ) {
            return;
        }

        Reseller_Helper::insert_ledger_entry(
            [
                'reseller_id' => $reseller_id,
                'order_id'    => $order_id,
                'type'        => 'shipping_debit',
                'amount'      => -1 * abs( $shipping_total ),
                'description' => sprintf( 'Shipping debit for refunded Order #%d', $order_id ),
            ]
        );
    }

    /**
     * Handle frontend withdrawal requests.
     *
     * @return void
     */
    public function handle_withdrawal_request() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! Reseller_Helper::is_reseller_approved( get_current_user_id() ) ) {
            wp_send_json_error( __( 'You are not allowed to request a withdrawal.', 'reseller-management' ), 403 );
        }

        global $wpdb;

        $reseller_id      = get_current_user_id();
        $amount           = round( (float) wp_unslash( $_POST['amount'] ?? 0 ), 2 );
        $payment_method   = sanitize_text_field( wp_unslash( $_POST['payment_method'] ?? '' ) );
        $account_details  = sanitize_textarea_field( wp_unslash( $_POST['account_details'] ?? '' ) );
        $current_balance  = Reseller_Helper::get_current_balance( $reseller_id );

        if ( $amount <= 0 || empty( $payment_method ) || empty( $account_details ) ) {
            wp_send_json_error( __( 'Please complete all withdrawal fields.', 'reseller-management' ), 422 );
        }

        if ( $amount > $current_balance ) {
            wp_send_json_error( __( 'Withdrawal amount exceeds your current balance.', 'reseller-management' ), 422 );
        }

        $inserted = $wpdb->insert(
            Reseller_Helper::get_withdrawals_table_name(),
            [
                'reseller_id'     => $reseller_id,
                'amount'          => $amount,
                'payment_method'  => $payment_method,
                'account_details' => $account_details,
                'status'          => 'pending',
                'created_at'      => current_time( 'mysql' ),
            ],
            [ '%d', '%f', '%s', '%s', '%s', '%s' ]
        );

        if ( ! $inserted ) {
            wp_send_json_error( __( 'Unable to store your withdrawal request.', 'reseller-management' ), 500 );
        }

        Reseller_Helper::insert_ledger_entry(
            [
                'reseller_id' => $reseller_id,
                'type'        => 'withdrawal_debit',
                'amount'      => -1 * abs( $amount ),
                'description' => sprintf( 'Withdrawal request #%d', (int) $wpdb->insert_id ),
            ]
        );

        wp_send_json_success( __( 'Withdrawal request submitted successfully.', 'reseller-management' ) );
    }
}
