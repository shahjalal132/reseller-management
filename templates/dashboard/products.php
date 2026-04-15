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

$product_items = [];

$build_item = static function ( $current_product, $current_post, $title, $category_ids, $parent_product = null ) {
	$image_id = (int) $current_product->get_image_id();
	if ( ! $image_id && $parent_product ) {
		$image_id = (int) $parent_product->get_image_id();
	}

	$image_url   = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
	$regular     = $current_product->get_regular_price();
	$sku         = $current_product->get_sku();
	$recommended = get_post_meta( $current_product->get_id(), '_reseller_recommended_price', true );

	$all_images = [];
	if ( $image_url ) {
		$all_images[] = $image_url;
	}

	$gallery_sources = [];
	if ( $parent_product ) {
		$gallery_sources[] = $parent_product;
	} else {
		$gallery_sources[] = $current_product;
	}

	foreach ( $gallery_sources as $gallery_product ) {
		$attachment_ids = $gallery_product->get_gallery_image_ids();
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

	$copy_text = $title;
	if ( $sku ) {
		$copy_text .= " ({$sku})";
	}
	$copy_text .= "\n";
	if ( $regular ) {
		$copy_text .= "Price: {$regular} TK\n";
	}
	if ( $recommended ) {
		$copy_text .= "Customer / Retail Price : {$recommended}\n";
	}

	$desc = wp_strip_all_tags( $current_post->post_content ?? '' );
	if ( ! $desc && $parent_product ) {
		$desc = wp_strip_all_tags( $parent_product->get_description() );
	}
	if ( $desc ) {
		$copy_text .= "\n" . $desc;
	}

	return [
		'id'           => $current_product->get_id(),
		'title'        => $title,
		'image_url'    => $image_url,
		'regular'      => $regular,
		'sku'          => $sku,
		'recommended'  => $recommended,
		'all_images'   => $all_images,
		'copy_text'    => $copy_text,
		'category_ids' => $category_ids,
	];
};

foreach ( $products as $product_post ) {
	$product = wc_get_product( $product_post->ID );
	if ( ! $product ) {
		continue;
	}

	$product_cat_ids = wp_get_post_terms( $product_post->ID, 'product_cat', [ 'fields' => 'ids' ] );
	if ( is_wp_error( $product_cat_ids ) ) {
		$product_cat_ids = [];
	}

	if ( $product->is_type( 'variable' ) ) {
		$variation_ids = $product->get_children();
		foreach ( $variation_ids as $variation_id ) {
			if ( 'publish' !== get_post_status( $variation_id ) ) {
				continue;
			}

			$variation_product = wc_get_product( $variation_id );
			if ( ! $variation_product || ! $variation_product->is_type( 'variation' ) ) {
				continue;
			}

			$variation_post = get_post( $variation_id );
			if ( ! $variation_post ) {
				continue;
			}

			$formatted_attributes = wc_get_formatted_variation( $variation_product, true, false, true );
			$variation_title      = $product->get_name();
			if ( $formatted_attributes ) {
				$variation_title .= ' - ' . wp_strip_all_tags( $formatted_attributes );
			}

			$product_items[] = $build_item( $variation_product, $variation_post, $variation_title, $product_cat_ids, $product );
		}
		continue;
	}

	$product_items[] = $build_item( $product, $product_post, $product_post->post_title, $product_cat_ids );
}

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
        <?php if ( empty( $product_items ) ) : ?>
            <p><?php esc_html_e( 'No products available yet.', 'reseller-management' ); ?></p>
        <?php else : ?>
            <?php foreach ( $product_items as $item ) : ?>
                <article class="rm-product-card" data-categories="<?php echo esc_attr( implode( ',', $item['category_ids'] ) ); ?>" data-sku="<?php echo esc_attr( $item['sku'] ); ?>">
                    <div class="rm-product-image">
                        <a href="<?php echo esc_url( add_query_arg( 'product_id', $item['id'] ) ); ?>">
                            <?php if ( $item['image_url'] ) : ?>
                                <img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>">
                            <?php else : ?>
                                <div class="rm-product-img-placeholder"></div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="rm-product-info">
                        <h4 class="rm-product-title" title="<?php echo esc_attr( $item['title'] ); ?>">
                            <a href="<?php echo esc_url( add_query_arg( 'product_id', $item['id'] ) ); ?>">
                                <?php echo esc_html( $item['title'] ); ?>
                            </a>
                        </h4>
                        <div class="rm-product-price-details">
                            <span class="rm-price-reg">Price: <?php echo esc_html( $item['regular'] ? $item['regular'] : '0' ); ?> TK</span>
                            <span class="rm-price-ret"><b>Customer / Retail Price : <?php echo esc_html( $item['recommended'] ? $item['recommended'] : '0' ); ?></b></span>
                        </div>
                        <div class="rm-product-actions">
                            <?php if ( ! empty( $item['all_images'] ) ) : ?>
                                <button class="rm-p-btn download-btn" type="button" title="<?php esc_attr_e( 'Download Images', 'reseller-management' ); ?>" data-images='<?php echo esc_attr( wp_json_encode( $item['all_images'] ) ); ?>'>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                </button>
                            <?php endif; ?>
                            <button class="rm-p-btn copy-btn" type="button" title="<?php esc_attr_e( 'Copy Details', 'reseller-management' ); ?>" data-copy="<?php echo esc_attr( $item['copy_text'] ); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
