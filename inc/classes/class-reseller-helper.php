<?php
/**
 * Shared helpers for reseller management flows.
 */

namespace BOILERPLATE\Inc;

class Reseller_Helper {

    /**
     * Get the reseller role slug.
     *
     * @return string
     */
    public static function get_role_slug() {
        return 'reseller';
    }

    /**
     * Get reseller ledger table name.
     *
     * @return string
     */
    public static function get_ledger_table_name() {
        global $wpdb;

        return $wpdb->prefix . 'reseller_ledger';
    }

    /**
     * Get reseller withdrawals table name.
     *
     * @return string
     */
    public static function get_withdrawals_table_name() {
        global $wpdb;

        return $wpdb->prefix . 'reseller_withdrawals';
    }

    /**
     * Ensure the reseller role exists.
     *
     * @return void
     */
    public static function maybe_register_role() {
        if ( get_role( self::get_role_slug() ) ) {
            return;
        }

        add_role(
            self::get_role_slug(),
            __( 'Reseller', 'reseller-management' ),
            [
                'read' => true,
            ]
        );
    }

    /**
     * Get supported reseller statuses.
     *
     * @return array<string>
     */
    public static function get_statuses() {
        return [ 'pending', 'approved', 'rejected', 'banned' ];
    }

    /**
     * Check whether a user is a reseller.
     *
     * @param \WP_User|int|null $user User instance or ID.
     *
     * @return bool
     */
    public static function is_reseller( $user ) {
        if ( is_numeric( $user ) ) {
            $user = get_user_by( 'id', (int) $user );
        }

        if ( ! $user instanceof \WP_User ) {
            return false;
        }

        return in_array( self::get_role_slug(), (array) $user->roles, true );
    }

    /**
     * Get the reseller status.
     *
     * @param int $user_id User ID.
     *
     * @return string
     */
    public static function get_reseller_status( $user_id ) {
        $status = (string) get_user_meta( $user_id, '_reseller_status', true );

        if ( ! in_array( $status, self::get_statuses(), true ) ) {
            $status = 'pending';
        }

        if ( self::is_currently_banned( $user_id ) ) {
            return 'banned';
        }

        return $status;
    }

    /**
     * Check if reseller is approved.
     *
     * @param int $user_id User ID.
     *
     * @return bool
     */
    public static function is_reseller_approved( $user_id ) {
        return 'approved' === self::get_reseller_status( $user_id );
    }

    /**
     * Check if reseller has an active ban.
     *
     * @param int $user_id User ID.
     *
     * @return bool
     */
    public static function is_currently_banned( $user_id ) {
        $banned_until = (int) get_user_meta( $user_id, '_reseller_banned_until', true );

        return $banned_until > time();
    }

    /**
     * Get the current balance from the ledger.
     *
     * @param int $user_id User ID.
     *
     * @return float
     */
    public static function get_current_balance( $user_id ) {
        global $wpdb;

        $table = self::get_ledger_table_name();
        $balance = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(amount), 0) FROM {$table} WHERE reseller_id = %d",
                $user_id
            )
        );

        return (float) $balance;
    }

    /**
     * Insert a ledger row.
     *
     * @param array<string, mixed> $data Ledger data.
     *
     * @return bool|int
     */
    public static function insert_ledger_entry( array $data ) {
        global $wpdb;

        $amount = isset( $data['amount'] ) ? round( (float) $data['amount'], 2 ) : 0;
        $inserted = $wpdb->insert(
            self::get_ledger_table_name(),
            [
                'reseller_id' => (int) $data['reseller_id'],
                'order_id'    => ! empty( $data['order_id'] ) ? (int) $data['order_id'] : 0,
                'type'        => sanitize_key( $data['type'] ),
                'amount'      => $amount,
                'description' => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
                'created_at'  => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%s', '%f', '%s', '%s' ]
        );

        return $inserted ? (int) $wpdb->insert_id : false;
    }

    /**
     * Get monthly profit rows for dashboard summaries.
     *
     * @param int $user_id User ID.
     *
     * @return array<int, object>
     */
    public static function get_monthly_profit_summary( $user_id ) {
        global $wpdb;

        $table = self::get_ledger_table_name();

        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE_FORMAT(created_at, '%%Y-%%m') AS month_key, COALESCE(SUM(amount), 0) AS total
                FROM {$table}
                WHERE reseller_id = %d
                GROUP BY DATE_FORMAT(created_at, '%%Y-%%m')
                ORDER BY month_key DESC
                LIMIT 6",
                $user_id
            )
        );
    }

    /**
     * Get reseller dashboard tabs.
     *
     * @return array<string, string>
     */
    public static function get_dashboard_tabs() {
        return [
            'dashboard' => [
                'label' => __( 'Dashboard', 'reseller-management' ),
                'icon'  => 'dashboard',
            ],
            'orders'    => [
                'label'    => __( 'Orders', 'reseller-management' ),
                'icon'     => 'orders',
                'children' => [
                    'all' => __( 'All Orders', 'reseller-management' ),
                    'add' => __( 'Add New Order', 'reseller-management' ),
                ],
            ],
            'products'  => [
                'label' => __( 'Products', 'reseller-management' ),
                'icon'  => 'products',
            ],
            'account'   => [
                'label' => __( 'Account', 'reseller-management' ),
                'icon'  => 'account',
            ],
            'settings'  => [
                'label' => __( 'Settings', 'reseller-management' ),
                'icon'  => 'settings',
            ],
            'customers' => [
                'label' => __( 'Customers', 'reseller-management' ),
                'icon'  => 'customers',
            ],
        ];
    }

    /**
     * Get user meta field map.
     *
     * @return array<string, string>
     */
    public static function get_profile_meta_map() {
        return [
            'phone'         => '_reseller_phone',
            'business_name' => '_reseller_business_name',
            'facebook_url'  => '_reseller_fb_url',
            'website_url'   => '_reseller_web_url',
            'nid_front_id'  => '_reseller_nid_front_id',
            'nid_back_id'   => '_reseller_nid_back_id',
        ];
    }
}
