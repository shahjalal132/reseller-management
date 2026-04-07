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
        'order'          => 'DESC',
    ]
);

$product_categories = get_terms( [
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
] );

$categories_hierarchy = [];
if ( ! is_wp_error( $product_categories ) && ! empty( $product_categories ) ) {
    foreach ( $product_categories as $cat ) {
        $categories_hierarchy[] = [
            'id'     => $cat->term_id,
            'name'   => $cat->name,
            'parent' => $cat->parent,
        ];
    }
}

?>
<div class="rm-products-wrapper">
    <script>
        window.rmProductCategories = <?php echo wp_json_encode( $categories_hierarchy ); ?>;
    </script>
    <div class="rm-products-filter">
        <div class="rm-filter-row rm-filter-top">
            <select class="rm-filter-limit">
                <option value="30">30</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">All</option>
            </select>
            <input type="text" class="rm-filter-search" placeholder="<?php esc_attr_e( 'search with product code || product name', 'reseller-management' ); ?>">
        </div>
        <div class="rm-filter-row rm-filter-bottom">
            <select class="rm-filter-cat">
                <option value=""><?php esc_html_e( 'select category', 'reseller-management' ); ?></option>
            </select>
            <select class="rm-filter-subcat">
                <option value=""><?php esc_html_e( 'select sub category', 'reseller-management' ); ?></option>
            </select>
            <select class="rm-filter-subsubcat">
                <option value=""><?php esc_html_e( 'select sub sub category', 'reseller-management' ); ?></option>
            </select>
        </div>
    </div>

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

                // Gather all images
                $all_images = [];
                if ( $image_url ) {
                    $all_images[] = $image_url;
                }
                if ( $product ) {
                    $attachment_ids = $product->get_gallery_image_ids();
                    if ( ! empty( $attachment_ids ) ) {
                        foreach ( $attachment_ids as $attachment_id ) {
                            $gallery_img = wp_get_attachment_image_url( $attachment_id, 'large' );
                            if ( $gallery_img ) {
                                $all_images[] = $gallery_img;
                            }
                        }
                    }
                }
                $all_images = array_values( array_unique( $all_images ) );

                // Prepare copy text
                $copy_text = $product_post->post_title . "\n";
                if ( $regular ) {
                    $copy_text .= "Price: {$regular} TK\n";
                }
                if ( $recommended ) {
                    $copy_text .= "Customer / Retail Price : {$recommended}\n";
                }
                $desc = wp_strip_all_tags( $product_post->post_content );
                if ( $desc ) {
                    $copy_text .= "\n" . $desc;
                }

                $product_cat_ids = wp_get_post_terms( $product_post->ID, 'product_cat', [ 'fields' => 'ids' ] );
                if ( is_wp_error( $product_cat_ids ) ) {
                    $product_cat_ids = [];
                }
                ?>
                <article class="rm-product-card" data-categories="<?php echo esc_attr( implode( ',', $product_cat_ids ) ); ?>">
                    <div class="rm-product-image">
                        <?php if ( $image_url ) : ?>
                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product_post->post_title ); ?>">
                        <?php else : ?>
                            <div class="rm-product-img-placeholder"></div>
                        <?php endif; ?>
                    </div>
                    <div class="rm-product-info">
                        <h4 class="rm-product-title" title="<?php echo esc_attr( $product_post->post_title ); ?>"><?php echo esc_html( $product_post->post_title ); ?></h4>
                        <div class="rm-product-price-details">
                            <span class="rm-price-reg">Price: <?php echo esc_html( $regular ? $regular : '0' ); ?> TK</span>
                            <span class="rm-price-ret"><b>Customer / Retail Price : <?php echo esc_html( $recommended ? $recommended : '0' ); ?></b></span>
                        </div>
                        <div class="rm-product-actions">
                            <?php if ( ! empty( $all_images ) ) : ?>
                                <button class="rm-p-btn download-btn" type="button" title="<?php esc_attr_e( 'Download Images', 'reseller-management' ); ?>" data-images='<?php echo esc_attr( wp_json_encode( $all_images ) ); ?>'>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                </button>
                            <?php endif; ?>
                            <button class="rm-p-btn copy-btn" type="button" title="<?php esc_attr_e( 'Copy Details', 'reseller-management' ); ?>" data-copy="<?php echo esc_attr( $copy_text ); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
