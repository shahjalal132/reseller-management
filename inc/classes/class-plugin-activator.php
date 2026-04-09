<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 */

class Plugin_Activator {

    public static function activate() {
        self::validate_dependencies();
        self::create_reseller_role();
        self::create_financial_tables();
        self::register_order_statuses();
        \BOILERPLATE\Inc\Reseller_Page_Manager::get_instance()->check_and_create_pages();
    }

    /**
     * Register custom order statuses.
     */
    protected static function register_order_statuses() {
        if ( class_exists( '\BOILERPLATE\Inc\Reseller_Orders' ) ) {
            \BOILERPLATE\Inc\Reseller_Orders::get_instance()->register_custom_order_statuses();
        }
    }

    /**
     * Validate required plugin dependencies.
     *
     * @return void
     */
    protected static function validate_dependencies() {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return;
        }

        deactivate_plugins( PLUGIN_BASE_NAME );

        wp_die(
            esc_html__( 'Reseller Management requires WooCommerce to be active before activation.', 'reseller-management' ),
            esc_html__( 'Plugin dependency missing', 'reseller-management' ),
            [
                'back_link' => true,
            ]
        );
    }

    /**
     * Register the reseller role.
     *
     * @return void
     */
    protected static function create_reseller_role() {
        \BOILERPLATE\Inc\Reseller_Helper::maybe_register_role();
    }

    /**
     * Create custom financial tables.
     *
     * @return void
     */
    protected static function create_financial_tables() {
        global $wpdb;

        \BOILERPLATE\Inc\Reseller_Helper::maybe_create_payment_methods_table();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate       = $wpdb->get_charset_collate();
        $ledger_table          = \BOILERPLATE\Inc\Reseller_Helper::get_ledger_table_name();
        $withdrawals_table     = \BOILERPLATE\Inc\Reseller_Helper::get_withdrawals_table_name();
        $payment_methods_table = \BOILERPLATE\Inc\Reseller_Helper::get_payment_methods_table_name();

        $sql_ledger = "CREATE TABLE {$ledger_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            reseller_id bigint(20) unsigned NOT NULL,
            order_id bigint(20) unsigned DEFAULT NULL,
            type varchar(50) NOT NULL,
            amount decimal(10,2) NOT NULL,
            description text DEFAULT NULL,
            reference varchar(191) NOT NULL DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY reseller_id (reseller_id),
            KEY order_id (order_id),
            KEY type (type)
        ) {$charset_collate};";

        $sql_withdrawals = "CREATE TABLE {$withdrawals_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            reseller_id bigint(20) unsigned NOT NULL,
            transaction_id varchar(50) NOT NULL,
            amount decimal(10,2) NOT NULL,
            payment_method varchar(50) NOT NULL,
            account_details text NOT NULL,
            note text DEFAULT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY reseller_id (reseller_id),
            KEY status (status)
        ) {$charset_collate};";

        $sql_payment_methods = "CREATE TABLE {$payment_methods_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            reseller_id bigint(20) unsigned NOT NULL,
            method_name varchar(20) NOT NULL,
            number varchar(50) NOT NULL,
            type varchar(20) NOT NULL DEFAULT 'personal',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY reseller_id (reseller_id)
        ) {$charset_collate};";

        dbDelta( $sql_ledger );
        dbDelta( $sql_withdrawals );
        dbDelta( $sql_payment_methods );
    }

}