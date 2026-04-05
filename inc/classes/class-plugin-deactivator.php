<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 */

class Plugin_Deactivator {

    public static function deactivate() {
        self::remove_reseller_role();
        self::drop_financial_tables();
        \BOILERPLATE\Inc\Reseller_Page_Manager::get_instance()->remove_pages();
    }

    /**
     * Remove the reseller role.
     *
     * @return void
     */
    protected static function remove_reseller_role() {
        remove_role( 'reseller' );
    }

    /**
     * Drop custom financial tables.
     *
     * @return void
     */
    protected static function drop_financial_tables() {
        global $wpdb;

        $ledger_table          = \BOILERPLATE\Inc\Reseller_Helper::get_ledger_table_name();
        $withdrawals_table     = \BOILERPLATE\Inc\Reseller_Helper::get_withdrawals_table_name();
        $payment_methods_table = \BOILERPLATE\Inc\Reseller_Helper::get_payment_methods_table_name();

        $wpdb->query( "DROP TABLE IF EXISTS {$ledger_table}" );
        $wpdb->query( "DROP TABLE IF EXISTS {$withdrawals_table}" );
        $wpdb->query( "DROP TABLE IF EXISTS {$payment_methods_table}" );
    }

}