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
        $is_reseller_page = false !== strpos( (string) $page_now, 'reseller-hub' );
        $is_order_page    = in_array( $page_now, [ 'post.php', 'post-new.php', 'woocommerce_page_wc-orders' ], true );

        if ( ! $is_reseller_page && ! $is_order_page ) {
            return;
        }

        // Only enqueue on order edit pages if it's an order
        if ( $is_order_page && ! isset( $_GET['post_type'] ) && ! isset( $_GET['post'] ) && $page_now !== 'woocommerce_page_wc-orders' ) {
             // HPOS uses woocommerce_page_wc-orders, traditional uses post.php?post=ID&action=edit
        }
        
        // Actually, just checking if it's the right screen is better
        $screen = get_current_screen();
        $is_order_screen = $screen && ( 'shop_order' === $screen->post_type || 'woocommerce_page_wc-orders' === $screen->id );

        if ( ! $is_reseller_page && ! $is_order_screen ) {
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
        wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.1', true );
        wp_enqueue_style( 'wpb-public-css', PLUGIN_PUBLIC_ASSETS_URL . '/css/public-style.css', [], time(), 'all' );
        wp_enqueue_script( 'wpb-public-js', PLUGIN_PUBLIC_ASSETS_URL . '/js/public-script.js', [ 'jquery' ], time(), true );
        wp_localize_script(
            'wpb-public-js',
            'rmPublic',
            [
                'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
                'ordersUrl'   => Reseller_Dashboard::get_instance()->get_dashboard_tab_url( 'orders', 'all' ),
                'nonce'       => wp_create_nonce( 'rm_public_nonce' ),
                'profit_data' => Reseller_Helper::get_monthly_profit_summary( get_current_user_id() ),
                'order_stats' => $this->get_order_stats_for_chart( get_current_user_id() ),
                'locations'   => $this->get_locations_data(),
                'productsPagination' => [
                    'prev' => __( '&laquo; Previous', 'reseller-management' ),
                    'next' => __( 'Next &raquo;', 'reseller-management' ),
                ],
            ]
        );
    }

    /**
     * Get order stats for the doughnut chart.
     *
     * @param int $user_id User ID.
     *
     * @return array<string, int>
     */
    private function get_order_stats_for_chart( $user_id ) {
        $orders = wc_get_orders( [ 'customer' => $user_id, 'limit' => -1 ] );
        $stats = [
            'completed' => 0,
            'pending'   => 0,
            'cancelled' => 0,
        ];

        foreach ( $orders as $order ) {
            $status = $order->get_status();
            if ( 'completed' === $status ) {
                $stats['completed']++;
            } elseif ( in_array( $status, [ 'pending', 'processing', 'on-hold' ], true ) ) {
                $stats['pending']++;
            } elseif ( in_array( $status, [ 'cancelled', 'failed', 'refunded' ], true ) ) {
                $stats['cancelled']++;
            }
        }

        return $stats;
    }

    /**
     * Get dynamic locations parsed from JSON.
     *
     * @return array
     */
    private function get_locations_data() {
        $districts_file = PLUGIN_BASE_PATH . '/assets/data/districts.json';
        $upazilas_file  = PLUGIN_BASE_PATH . '/assets/data/upazilas.json';

        $districts    = [];
        $thanas       = [];
        $district_map = [];

        if ( file_exists( $districts_file ) ) {
            $data = json_decode( file_get_contents( $districts_file ), true );
            if ( is_array( $data ) ) {
                foreach ( $data as $section ) {
                    if ( isset( $section['type'] ) && 'table' === $section['type'] && 'districts' === $section['name'] && ! empty( $section['data'] ) ) {
                        foreach ( $section['data'] as $d ) {
                            $district_map[ $d['id'] ] = $d['name'];
                            $districts[] = $d['name'];
                        }
                    }
                }
            }
        }

        if ( file_exists( $upazilas_file ) ) {
            $data = json_decode( file_get_contents( $upazilas_file ), true );
            if ( is_array( $data ) ) {
                foreach ( $data as $section ) {
                    if ( isset( $section['type'] ) && 'table' === $section['type'] && 'upazilas' === $section['name'] && ! empty( $section['data'] ) ) {
                        foreach ( $section['data'] as $u ) {
                            $district_id = $u['district_id'];
                            if ( isset( $district_map[ $district_id ] ) ) {
                                $district_name = $district_map[ $district_id ];
                                if ( ! isset( $thanas[ $district_name ] ) ) {
                                    $thanas[ $district_name ] = [];
                                }
                                $thanas[ $district_name ][] = $u['name'];
                            }
                        }
                    }
                }
            }
        }

        sort( $districts );
        foreach ( $thanas as $key => $thana_list ) {
            sort( $thanas[ $key ] );
        }

        return [
            'districts' => $districts,
            'thanas'    => $thanas,
        ];
    }
}