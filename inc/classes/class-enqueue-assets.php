<?php

/**
 * Enqueue Plugin Admin and Public Assets
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Enqueue_Assets {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        $this->setup_hooks();
    }

    /**
     * Setup enqueue hooks.
     *
     * @return void
     */
    public function setup_hooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
    }

    /**
     * Enqueue Admin Assets.
     * @param mixed $page_now Current page
     * @return void
     */
    public function enqueue_admin_assets( $page_now ) {
        if ( false === strpos( (string) $page_now, 'reseller-hub' ) ) {
            return;
        }

        wp_enqueue_style( 'toast', PLUGIN_ADMIN_ASSETS_DIR_URL . '/css/toast.css', [], false, 'all' );
        wp_enqueue_style( 'wpb-admin-css', PLUGIN_ADMIN_ASSETS_DIR_URL . '/css/admin-style.css', [], time(), 'all' );
        wp_enqueue_script( 'wpb-admin-js', PLUGIN_ADMIN_ASSETS_DIR_URL . '/js/admin-script.js', [ 'jquery' ], time(), true );
        wp_localize_script(
            'wpb-admin-js',
            'wpb_admin_localize',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            ]
        );
    }

    /**
     * Enqueue Public Assets.
     * @return void
     */
    public function enqueue_public_assets() {
        wp_enqueue_style( 'wpb-public-css', PLUGIN_PUBLIC_ASSETS_URL . '/css/public-style.css', [], time(), 'all' );
        wp_enqueue_script( 'wpb-public-js', PLUGIN_PUBLIC_ASSETS_URL . '/js/public-script.js', [ 'jquery' ], time(), true );
        wp_localize_script(
            'wpb-public-js',
            'rmPublic',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'rm_public_nonce' ),
            ]
        );
    }
}