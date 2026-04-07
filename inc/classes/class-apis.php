<?php

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Credentials_Options;
use BOILERPLATE\Inc\Traits\Program_Logs;
use BOILERPLATE\Inc\Traits\Singleton;

class APIS {

    use Singleton;
    use Program_Logs;
    use Credentials_Options;

    public function __construct() {
        // $this->load_credentials_options();
        $this->setup_hooks();
    }

    public function setup_hooks() {
        // Register custom REST API routes
        add_action( 'rest_api_init', [ $this, 'register_apis_routes' ] );
    }

    public function register_apis_routes() {

        // register health check route
        register_rest_route( 'api/v1', '/health', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'health_check_callback' ],
            'permission_callback' => '__return_true', // Allow public access
        ] );

        // register seed route
        register_rest_route( 'api/v1', '/seed', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'seed_data_callback' ],
            'permission_callback' => '__return_true', // Allow public access
        ] );
    }

    public function health_check_callback() {
        return new \WP_REST_Response( [
            'status'    => 'success',
            'message'   => 'API is healthy and running',
            'timestamp' => current_time( 'mysql' ),
        ], 200 );
    }

    public function seed_data_callback() {
        global $wpdb;

        $ledger_table          = \BOILERPLATE\Inc\Reseller_Helper::get_ledger_table_name();
        $withdrawals_table     = \BOILERPLATE\Inc\Reseller_Helper::get_withdrawals_table_name();
        $payment_methods_table = \BOILERPLATE\Inc\Reseller_Helper::get_payment_methods_table_name();

        $reseller_id = 2;

        // Clear existing data for reseller
        $wpdb->delete( $ledger_table, [ 'reseller_id' => $reseller_id ] );
        $wpdb->delete( $withdrawals_table, [ 'reseller_id' => $reseller_id ] );
        $wpdb->delete( $payment_methods_table, [ 'reseller_id' => $reseller_id ] );

        // Seed Payment Methods (IDs 1, 2, 3)
        $payment_methods = [
            [ 'method_name' => 'bKash', 'number' => '01711111111', 'type' => 'personal' ],
            [ 'method_name' => 'Nagad', 'number' => '01822222222', 'type' => 'agent' ],
            [ 'method_name' => 'Bank', 'number' => '123456789', 'type' => 'personal' ],
        ];
        
        foreach ( $payment_methods as $index => $pm ) {
            $wpdb->insert( $payment_methods_table, [
                'id'          => $index + 1,
                'reseller_id' => $reseller_id,
                'method_name' => $pm['method_name'],
                'number'      => $pm['number'],
                'type'        => $pm['type'],
                'created_at'  => current_time( 'mysql' ),
            ] );
        }

        // Seed 100 Orders in Ledger
        for ( $i = 1; $i <= 100; $i++ ) {
            $wpdb->insert( $ledger_table, [
                'reseller_id' => $reseller_id,
                'order_id'    => 1000 + $i,
                'type'        => 'commission',
                'amount'      => rand( 50, 500 ),
                'description' => 'Commission for order #' . (1000 + $i),
                'created_at'  => current_time( 'mysql' ),
            ] );
        }

        // Seed 30 Withdrawals
        for ( $i = 1; $i <= 30; $i++ ) {
            $wpdb->insert( $withdrawals_table, [
                'reseller_id'     => $reseller_id,
                'transaction_id'  => 'TXN' . rand( 100000, 999999 ),
                'amount'          => rand( 500, 2000 ),
                'payment_method'  => 'bKash',
                'account_details' => '01711111111',
                'note'            => 'Sample withdrawal request ' . $i,
                'status'          => 'pending',
                'created_at'      => current_time( 'mysql' ),
            ] );
        }

        return new \WP_REST_Response( [
            'status'  => 'success',
            'message' => 'Data seeded successfully for reseller ID ' . $reseller_id,
        ], 200 );
    }

}