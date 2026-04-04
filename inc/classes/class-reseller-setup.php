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
        add_action( 'admin_notices', [ $this, 'maybe_show_woocommerce_notice' ] );
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
