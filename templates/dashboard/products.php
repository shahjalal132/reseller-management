<?php
/**
 * Products tab.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$products = get_posts(
    [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]
);
?>
<div class="rm-card">
    <h3><?php esc_html_e( 'Products', 'reseller-management' ); ?></h3>
    <div class="rm-product-grid">
        <?php if ( empty( $products ) ) : ?>
            <p><?php esc_html_e( 'No products available yet.', 'reseller-management' ); ?></p>
        <?php else : ?>
            <?php foreach ( $products as $product_post ) : ?>
                <?php
                $product      = wc_get_product( $product_post->ID );
                $image_url    = get_the_post_thumbnail_url( $product_post->ID, 'large' );
                $regular      = $product ? $product->get_regular_price() : '';
                $recommended  = get_post_meta( $product_post->ID, '_reseller_recommended_price', true );
                ?>
                <article class="rm-product-card">
                    <div class="rm-product-image">
                        <?php if ( $image_url ) : ?>
                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product_post->post_title ); ?>">
                        <?php endif; ?>
                    </div>
                    <h4><?php echo esc_html( $product_post->post_title ); ?></h4>
                    <p><?php printf( esc_html__( 'Regular Price: %s', 'reseller-management' ), wp_strip_all_tags( wc_price( (float) $regular ) ) ); ?></p>
                    <p><?php printf( esc_html__( 'Recommended Price: %s', 'reseller-management' ), wp_strip_all_tags( wc_price( (float) $recommended ) ) ); ?></p>
                    <?php if ( $image_url ) : ?>
                        <a class="rm-button rm-button-secondary" href="<?php echo esc_url( $image_url ); ?>" download>
                            <?php esc_html_e( 'Download Image', 'reseller-management' ); ?>
                        </a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
