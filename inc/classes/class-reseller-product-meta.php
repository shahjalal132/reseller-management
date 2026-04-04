<?php
/**
 * Product meta fields for reseller pricing.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Product_Meta {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        add_action( 'woocommerce_product_options_pricing', [ $this, 'register_product_fields' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_fields' ] );
    }

    /**
     * Render extra product fields.
     *
     * @return void
     */
    public function register_product_fields() {
        woocommerce_wp_text_input(
            [
                'id'                => '_reseller_recommended_price',
                'label'             => __( 'Reseller Recommended Price', 'reseller-management' ),
                'type'              => 'number',
                'desc_tip'          => true,
                'custom_attributes' => [
                    'step' => '0.01',
                    'min'  => '0',
                ],
                'description'       => __( 'Suggested selling price for resellers.', 'reseller-management' ),
            ]
        );
    }

    /**
     * Save extra product fields.
     *
     * @param int $product_id Product ID.
     *
     * @return void
     */
    public function save_product_fields( $product_id ) {
        $recommended_price = round( (float) wp_unslash( $_POST['_reseller_recommended_price'] ?? 0 ), 2 );
        update_post_meta( $product_id, '_reseller_recommended_price', $recommended_price );
    }
}
