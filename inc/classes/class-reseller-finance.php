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
        add_action( 'woocommerce_order_status_delivered', [ $this, 'credit_order_commission' ] );
        add_action( 'woocommerce_order_status_refunded', [ $this, 'debit_order_shipping' ] );
        add_action( 'wp_ajax_reseller_request_withdrawal', [ $this, 'handle_withdrawal_request' ] );
        add_action( 'wp_ajax_reseller_save_payment_method', [ $this, 'handle_save_payment_method' ] );
        add_action( 'wp_ajax_reseller_delete_payment_method', [ $this, 'handle_delete_payment_method' ] );
        add_action( 'wp_ajax_rm_admin_update_withdrawal_status', [ $this, 'handle_admin_update_withdrawal_status' ] );

        // COD Deduction.
        add_action( 'woocommerce_order_status_delivered', [ $this, 'apply_cod_deduction' ] );
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
     * Get ledger rows for a reseller with optional pagination.
     *
     * @param int $user_id User ID.
     * @param int $limit   Optional limit.
     * @param int $offset  Optional offset.
     *
     * @return array<int, object>
     */
    public static function get_transactions( $user_id, $limit = 0, $offset = 0 ) {
        global $wpdb;

        $table = Reseller_Helper::get_ledger_table_name();
        $query = "SELECT * FROM {$table} WHERE reseller_id = %d ORDER BY created_at DESC, id DESC";

        if ( $limit > 0 ) {
            $query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $limit, $offset );
        }

        return (array) $wpdb->get_results( $wpdb->prepare( $query, $user_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Get total number of transactions for a reseller.
     *
     * @param int $user_id User ID.
     *
     * @return int
     */
    public static function get_total_transactions_count( $user_id ) {
        global $wpdb;

        $table = Reseller_Helper::get_ledger_table_name();

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(id) FROM {$table} WHERE reseller_id = %d",
                $user_id
            )
        );
    }

    /**
     * Sum transaction amounts before a certain offset in DESC order.
     * Used for calculating running balance correctly in paginated views.
     *
     * @param int $user_id User ID.
     * @param int $offset  Offset.
     *
     * @return float
     */
    public static function get_transactions_sum_before_offset( $user_id, $offset ) {
        if ( $offset <= 0 ) {
            return 0.0;
        }

        global $wpdb;
        $table = Reseller_Helper::get_ledger_table_name();

        // Calculate sum of amounts for the first $offset rows in the DESC sort order.
        return (float) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(amount), 0) FROM (
                    SELECT amount FROM {$table} 
                    WHERE reseller_id = %d 
                    ORDER BY created_at DESC, id DESC 
                    LIMIT %d
                ) as sub",
                $user_id,
                $offset
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
        $note             = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
        $current_balance  = Reseller_Helper::get_current_balance( $reseller_id );

        if ( $amount <= 0 || empty( $payment_method ) || empty( $account_details ) ) {
            wp_send_json_error( __( 'Please complete all withdrawal fields.', 'reseller-management' ), 422 );
        }

        if ( $amount > $current_balance ) {
            wp_send_json_error( __( 'Withdrawal amount exceeds your current balance.', 'reseller-management' ), 422 );
        }

        $min_reserve      = Reseller_Helper::get_minimum_balance_reserve();
        $max_withdrawable = Reseller_Helper::get_max_withdrawable_amount( $current_balance );

        if ( round( $amount, 2 ) > round( $max_withdrawable, 2 ) ) {
            wp_send_json_error(
                sprintf(
                    /* translators: 1: formatted maximum withdrawable amount, 2: formatted minimum balance that must remain */
                    __( 'The maximum you can withdraw is %1$s (your balance must stay at or above %2$s).', 'reseller-management' ),
                    wp_strip_all_tags( wc_price( $max_withdrawable ) ),
                    wp_strip_all_tags( wc_price( $min_reserve ) )
                ),
                422
            );
        }

        $transaction_id = 'TXN-' . strtoupper( uniqid() );

        $inserted = $wpdb->insert(
            Reseller_Helper::get_withdrawals_table_name(),
            [
                'reseller_id'     => $reseller_id,
                'transaction_id'  => $transaction_id,
                'amount'          => $amount,
                'payment_method'  => $payment_method,
                'account_details' => $account_details,
                'note'            => $note,
                'status'          => 'pending',
                'created_at'      => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s' ]
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

    /**
     * Handle save (add or update) a payment method.
     *
     * @return void
     */
    public function handle_save_payment_method() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! Reseller_Helper::is_reseller_approved( get_current_user_id() ) ) {
            wp_send_json_error( __( 'You are not allowed to manage payment methods.', 'reseller-management' ), 403 );
        }

        global $wpdb;

        $reseller_id = get_current_user_id();
        $id          = (int) ( $_POST['id'] ?? 0 );
        $method_name = sanitize_key( wp_unslash( $_POST['method_name'] ?? '' ) );
        $number      = sanitize_text_field( wp_unslash( $_POST['number'] ?? '' ) );
        $type        = sanitize_key( wp_unslash( $_POST['type'] ?? '' ) );

        $allowed_methods = [ 'bkash', 'nagad', 'rocket' ];
        $allowed_types   = [ 'agent', 'personal' ];

        if ( ! in_array( $method_name, $allowed_methods, true ) || empty( $number ) || ! in_array( $type, $allowed_types, true ) ) {
            wp_send_json_error( __( 'Please provide valid payment method details.', 'reseller-management' ), 422 );
        }

        $table = Reseller_Helper::get_payment_methods_table_name();
        $data  = [
            'reseller_id' => $reseller_id,
            'method_name' => $method_name,
            'number'      => $number,
            'type'        => $type,
        ];
        $formats = [ '%d', '%s', '%s', '%s' ];

        if ( $id > 0 ) {
            // Ensure ownership.
            $existing = $wpdb->get_var( $wpdb->prepare( "SELECT reseller_id FROM {$table} WHERE id = %d", $id ) );
            if ( (int) $existing !== $reseller_id ) {
                wp_send_json_error( __( 'You are not allowed to edit this payment method.', 'reseller-management' ), 403 );
            }

            $wpdb->update( $table, $data, [ 'id' => $id ], $formats, [ '%d' ] );
            wp_send_json_success( __( 'Payment method updated successfully.', 'reseller-management' ) );
        }

        $wpdb->insert( $table, $data, $formats );
        wp_send_json_success( __( 'Payment method added successfully.', 'reseller-management' ) );
    }

    /**
     * Handle delete a payment method.
     *
     * @return void
     */
    public function handle_delete_payment_method() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! Reseller_Helper::is_reseller_approved( get_current_user_id() ) ) {
            wp_send_json_error( __( 'You are not allowed to delete payment methods.', 'reseller-management' ), 403 );
        }

        global $wpdb;

        $reseller_id = get_current_user_id();
        $id          = (int) ( $_POST['id'] ?? 0 );

        if ( $id <= 0 ) {
            wp_send_json_error( __( 'Invalid payment method ID.', 'reseller-management' ), 422 );
        }

        $table    = Reseller_Helper::get_payment_methods_table_name();
        $existing = $wpdb->get_var( $wpdb->prepare( "SELECT reseller_id FROM {$table} WHERE id = %d", $id ) );

        if ( (int) $existing !== $reseller_id ) {
            wp_send_json_error( __( 'You are not allowed to delete this payment method.', 'reseller-management' ), 403 );
        }

        $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
        wp_send_json_success( __( 'Payment method deleted successfully.', 'reseller-management' ) );
    }

    /**
     * Handle updating withdrawal status from admin dashboard via AJAX.
     *
     * @return void
     */
    public function handle_admin_update_withdrawal_status() {
        check_ajax_referer( 'rm_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'You are not allowed to update withdrawal statuses.', 'reseller-management' ), 403 );
        }

        global $wpdb;

        $wd_id  = (int) ( $_POST['wd_id'] ?? 0 );
        $status = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );

        $allowed_statuses = [ 'pending', 'approved', 'rejected', 'completed' ];

        if ( $wd_id <= 0 || ! in_array( $status, $allowed_statuses, true ) ) {
            wp_send_json_error( __( 'Invalid request details.', 'reseller-management' ), 422 );
        }

        $table = Reseller_Helper::get_withdrawals_table_name();
        
        $updated = $wpdb->update(
            $table,
            [ 'status' => $status ],
            [ 'id'     => $wd_id ],
            [ '%s' ],
            [ '%d' ]
        );

        if ( false === $updated ) {
            wp_send_json_error( __( 'Database update failed.', 'reseller-management' ), 500 );
        }

        wp_send_json_success( __( 'Withdrawal status updated successfully.', 'reseller-management' ) );
    }

    /**
     * Apply COD deduction when an order is delivered.
     *
     * @param int $order_id Order ID.
     *
     * @return void
     */
    public function apply_cod_deduction( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $reseller_id = (int) $order->get_meta( '_assigned_reseller_id', true );
        if ( ! $reseller_id || self::ledger_entry_exists_for_order( $order_id, 'cod_deduction' ) ) {
            return;
        }

        $settings = get_option( 'rm_settings', [] );
        if ( ( $settings['cod_enabled'] ?? 'no' ) !== 'yes' ) {
            return;
        }

        $percentage = (float) ( $settings['cod_input1'] ?? 0 );
        if ( $percentage <= 0 ) {
            return;
        }

        $total     = (float) $order->get_total();
        $deduction = ( $total * $percentage ) / 100;

        if ( $deduction <= 0 ) {
            return;
        }

        Reseller_Helper::insert_ledger_entry(
            [
                'reseller_id' => $reseller_id,
                'order_id'    => $order_id,
                'type'        => 'cod_deduction',
                'amount'      => -1 * abs( $deduction ),
                'description' => sprintf( 'COD Deduction (%s%%) for Order #%d', $percentage, $order_id ),
            ]
        );
    }
}
