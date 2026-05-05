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

        add_action( 'woocommerce_variation_options_pricing', [ $this, 'register_variation_fields' ], 10, 3 );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_fields' ], 10, 2 );
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

        woocommerce_wp_text_input(
            [
                'id'          => '_reseller_product_video_url',
                'label'       => __( 'Product Video URL', 'reseller-management' ),
                'desc_tip'    => true,
                'description' => __( 'Add a YouTube or Vimeo URL for the product video.', 'reseller-management' ),
                'placeholder' => __( 'https://www.youtube.com/watch?v=...', 'reseller-management' ),
            ]
        );
    }

    /**
     * Save extra product fields (simple, external, etc. — not variable parent or variations).
     *
     * @param int $product_id Product ID.
     *
     * @return void
     */
    public function save_product_fields( $product_id ) {
        $product = wc_get_product( $product_id );
        if ( ! $product || $product->is_type( 'variation' ) || $product->is_type( 'variable' ) ) {
            return;
        }

        $recommended_price = round( (float) wc_format_decimal( wp_unslash( $_POST['_reseller_recommended_price'] ?? '' ) ), 2 );
        update_post_meta( $product_id, '_reseller_recommended_price', $recommended_price );

        if ( isset( $_POST['_reseller_product_video_url'] ) ) {
            update_post_meta( $product_id, '_reseller_product_video_url', esc_url_raw( wp_unslash( $_POST['_reseller_product_video_url'] ) ) );
        }
    }

    /**
     * Render reseller recommended price on each variation (variable products).
     *
     * @param int     $loop           Variation loop index.
     * @param array   $variation_data Variation data.
     * @param WP_Post $variation      Variation post.
     *
     * @return void
     */
    public function register_variation_fields( $loop, $variation_data, $variation ) {
        $stored = get_post_meta( $variation->ID, '_reseller_recommended_price', true );
        $value  = ( '' !== $stored && null !== $stored ) ? wc_format_localized_price( (string) $stored ) : '';

        $label = sprintf(
            /* translators: %s: currency symbol */
            __( 'Reseller recommended price (%s)', 'reseller-management' ),
            get_woocommerce_currency_symbol()
        );

        woocommerce_wp_text_input(
            [
                'id'            => "variable_reseller_recommended_price_{$loop}",
                'name'          => "variable_reseller_recommended_price[{$loop}]",
                'value'         => $value,
                'label'         => $label,
                'data_type'     => 'price',
                'desc_tip'      => true,
                'description'   => __( 'Suggested selling price for resellers for this variation.', 'reseller-management' ),
                'wrapper_class' => 'form-row form-row-full',
                'placeholder'   => __( 'Optional', 'reseller-management' ),
            ]
        );

        $video_url = get_post_meta( $variation->ID, '_reseller_product_video_url', true );
        woocommerce_wp_text_input(
            [
                'id'            => "variable_reseller_product_video_url_{$loop}",
                'name'          => "variable_reseller_product_video_url[{$loop}]",
                'value'         => $video_url,
                'label'         => __( 'Product Video URL', 'reseller-management' ),
                'desc_tip'      => true,
                'description'   => __( 'Add a YouTube or Vimeo URL for this variation video.', 'reseller-management' ),
                'wrapper_class' => 'form-row form-row-full',
                'placeholder'   => __( 'https://www.youtube.com/watch?v=...', 'reseller-management' ),
            ]
        );
    }

    /**
     * Persist variation reseller recommended price.
     *
     * @param int $variation_id Variation product ID.
     * @param int $loop         Loop index matching POST arrays.
     *
     * @return void
     */
    public function save_variation_fields( $variation_id, $loop ) {
        if ( isset( $_POST['variable_reseller_recommended_price'][ $loop ] ) ) {
            $raw = wp_unslash( $_POST['variable_reseller_recommended_price'][ $loop ] );
            if ( '' === $raw || null === $raw ) {
                delete_post_meta( $variation_id, '_reseller_recommended_price' );
            } else {
                $recommended_price = round( (float) wc_format_decimal( $raw ), 2 );
                update_post_meta( $variation_id, '_reseller_recommended_price', $recommended_price );
            }
        }

        if ( isset( $_POST['variable_reseller_product_video_url'][ $loop ] ) ) {
            $video_url = esc_url_raw( wp_unslash( $_POST['variable_reseller_product_video_url'][ $loop ] ) );
            update_post_meta( $variation_id, '_reseller_product_video_url', $video_url );
        }
    }
}
