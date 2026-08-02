<?php
/**
 * Runtime setup and dependency notices.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Setup {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        add_action( 'init', [ $this, 'register_reseller_role' ] );
        add_action( 'admin_init', [ $this, 'maybe_upgrade_ledger_schema' ] );
        add_action( 'admin_init', [ $this, 'maybe_upgrade_shop_tables' ] );
        add_action( 'admin_notices', [ $this, 'maybe_show_woocommerce_notice' ] );
    }

    /**
     * Ensure ledger table has columns expected by current plugin version.
     *
     * @return void
     */
    public function maybe_upgrade_ledger_schema() {
        Reseller_Helper::maybe_upgrade_ledger_reference_column();
    }

    /**
     * Ensure My Shop tables exist for existing installs.
     *
     * @return void
     */
    public function maybe_upgrade_shop_tables() {
        if ( '1.0.0' === get_option( 'rm_shop_tables_version', '' ) ) {
            return;
        }

        Reseller_Helper::maybe_create_shop_tables();
    }

    /**
     * Ensure the reseller role exists at runtime.
     *
     * @return void
     */
    public function register_reseller_role() {
        Reseller_Helper::maybe_register_role();
    }

    /**
     * Show an admin warning when WooCommerce is inactive.
     *
     * @return void
     */
    public function maybe_show_woocommerce_notice() {
        if ( class_exists( 'WooCommerce' ) ) {
            return;
        }

        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            esc_html__( 'Reseller Management works only when WooCommerce is active.', 'reseller-management' )
        );
    }
}
