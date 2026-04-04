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
                'locations'   => [
                    'districts' => [
                        'Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Barisal', 'Sylhet', 'Rangpur', 'Mymensingh',
                        'Bagerhat', 'Bandarban', 'Barguna', 'Bhola', 'Bogra', 'Brahmanbaria', 'Chandpur', 'Chapainawabganj',
                        'Chuadanga', 'Comilla', 'Cox\'s Bazar', 'Dinajpur', 'Faridpur', 'Feni', 'Gaibandha', 'Gazipur',
                        'Gopalganj', 'Habiganj', 'Jamalpur', 'Jessore', 'Jhalokati', 'Jhenaidah', 'Joypurhat', 'Khagrachhari',
                        'Kishoreganj', 'Kurigram', 'Kushtia', 'Lakshmipur', 'Lalmonirhat', 'Madaripur', 'Magura', 'Manikganj',
                        'Meherpur', 'Moulvibazar', 'Munshiganj', 'Naogaon', 'Narail', 'Narayanganj', 'Narsingdi', 'Natore',
                        'Netrokona', 'Nilphamari', 'Noakhali', 'Pabna', 'Panchagarh', 'Patuakhali', 'Pirojpur', 'Rajbari', 'Shariatpur',
                        'Sherpur', 'Sirajganj', 'Sunamganj', 'Tangail', 'Thakurgaon'
                    ],
                    'thanas' => [
                        'Dhaka' => ['Abdullahpur', 'Uttara', 'Mirpur', 'Gulshan', 'Banani', 'Dhanmondi', 'Motijheel', 'Badda', 'Khilgaon', 'Basundhara'],
                        'Chittagong' => ['Pahartali', 'Kotwali', 'Double Mooring', 'Bandar', 'Panchlaish', 'Bakalia', 'Chandgaon'],
                        'Comilla' => ['Laksam', 'Comilla Sadar', 'Barura', 'Chandina', 'Daudkandi', 'Homna', 'Muradnagar'],
                        // Simplified for now, can be expanded or fetched via AJAX if needed
                    ]
                ]
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
}