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
		'post_parent'    => 0,
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
		'variations'   => [],
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
		$variation_rows = [];
		foreach ( $product->get_available_variations() as $variation_data ) {
			$variation_id = (int) ( $variation_data['variation_id'] ?? 0 );
			if ( ! $variation_id ) {
				continue;
			}

			$variation_product = wc_get_product( $variation_id );
			if ( ! $variation_product || ! $variation_product->is_type( 'variation' ) ) {
				continue;
			}

			$variation_post = get_post( $variation_id );
			if ( ! $variation_post || 'publish' !== $variation_post->post_status ) {
				continue;
			}

			$variation_label = wc_get_formatted_variation( $variation_product, true, false, true );
			if ( ! $variation_label ) {
				$label_parts = [];
				$raw_attrs   = $variation_data['attributes'] ?? [];

				foreach ( $raw_attrs as $raw_key => $raw_value ) {
					if ( '' === (string) $raw_value ) {
						continue;
					}

					$taxonomy   = str_replace( 'attribute_', '', (string) $raw_key );
					$attr_label = wc_attribute_label( $taxonomy );
					if ( ! $attr_label || $attr_label === $taxonomy ) {
						$attr_label = ucwords( str_replace( [ 'pa_', '_' ], [ '', ' ' ], $taxonomy ) );
					}

					$display_value = (string) $raw_value;
					if ( taxonomy_exists( $taxonomy ) ) {
						$term = get_term_by( 'slug', (string) $raw_value, $taxonomy );
						if ( $term && ! is_wp_error( $term ) ) {
							$display_value = $term->name;
						}
					}

					$label_parts[] = sprintf( '%s: %s', $attr_label, $display_value );
				}

				if ( ! empty( $label_parts ) ) {
					$variation_label = implode( ', ', $label_parts );
				} else {
					$variation_label = sprintf(
						/* translators: %d: variation ID. */
						__( 'Variation #%d', 'reseller-management' ),
						$variation_id
					);
				}
			}

			$variation_label = wp_strip_all_tags( $variation_label );

			$v_regular     = $variation_product->get_regular_price();
			$v_recommended = $variation_product->get_meta( '_reseller_recommended_price' );
			if ( '' === $v_recommended || null === $v_recommended ) {
				$v_recommended = $variation_product->get_price();
			}

			$v_sku = $variation_product->get_sku();

			$v_image_id = (int) $variation_product->get_image_id();
			$v_image    = $v_image_id ? wp_get_attachment_image_url( $v_image_id, 'large' ) : '';

			$v_copy = $product->get_name();
			if ( $v_sku ) {
				$v_copy .= " ({$v_sku})";
			}
			$v_copy .= "\n";
			if ( $v_regular ) {
				$v_copy .= "Price: {$v_regular} TK\n";
			}
			if ( $v_recommended ) {
				$v_copy .= "Customer / Retail Price : {$v_recommended}\n";
			}
			$v_copy .= $variation_label . "\n";
			$desc    = wp_strip_all_tags( $variation_post->post_content ?? '' );
			if ( ! $desc ) {
				$desc = wp_strip_all_tags( $product->get_description() );
			}
			if ( $desc ) {
				$v_copy .= "\n" . $desc;
			}

			$variation_rows[] = [
				'id'            => $variation_id,
				'label'         => $variation_label,
				'regular'       => $v_regular,
				'recommended'   => $v_recommended,
				'sku'           => $v_sku,
				'image_url'     => $v_image,
				'copy_text'     => $v_copy,
			];
		}

		if ( ! empty( $variation_rows ) ) {
			$first = $variation_rows[0];
			$first_variation = wc_get_product( $first['id'] );
			if ( $first_variation ) {
				$card = $build_item( $first_variation, get_post( $first['id'] ), $product_post->post_title, $product_cat_ids, $product );
				$card['id']          = $product->get_id();
				$card['title']       = $product_post->post_title;
				$card['variations']  = $variation_rows;
				$card['regular']     = $first['regular'];
				$card['recommended'] = $first['recommended'];
				$card['copy_text']   = $first['copy_text'];
				$sku_parts           = array_filter( array_column( $variation_rows, 'sku' ) );
				$card['sku']         = implode( ' ', $sku_parts );
				$product_items[]     = $card;
			}
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
            <div class="rm-filter-per-page">
                <label for="rm-products-per-page"><?php esc_html_e( 'Products per page', 'reseller-management' ); ?></label>
                <select id="rm-products-per-page" class="rm-filter-limit" aria-label="<?php esc_attr_e( 'Number of products to show per page', 'reseller-management' ); ?>">
                    <option value="35"><?php echo esc_html( sprintf( /* translators: %d: number of products */ __( '%d per page', 'reseller-management' ), 35 ) ); ?></option>
                    <option value="70"><?php echo esc_html( sprintf( __( '%d per page', 'reseller-management' ), 70 ) ); ?></option>
                    <option value="105"><?php echo esc_html( sprintf( __( '%d per page', 'reseller-management' ), 105 ) ); ?></option>
                    <option value="all"><?php esc_html_e( 'All', 'reseller-management' ); ?></option>
                </select>
            </div>
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
                <?php
                $has_variations = ! empty( $item['variations'] );
                $detail_product_id = $has_variations ? (int) $item['variations'][0]['id'] : (int) $item['id'];
                $detail_url        = add_query_arg( 'product_id', $detail_product_id );
                ?>
                <article class="rm-product-card<?php echo $has_variations ? ' rm-product-card--variable' : ''; ?>" data-categories="<?php echo esc_attr( implode( ',', $item['category_ids'] ) ); ?>" data-sku="<?php echo esc_attr( $item['sku'] ); ?>"<?php echo $has_variations && ! empty( $item['image_url'] ) ? ' data-default-image="' . esc_url( $item['image_url'] ) . '"' : ''; ?>>
                    <div class="rm-product-image">
                        <a class="rm-product-card-detail-link" href="<?php echo esc_url( $detail_url ); ?>">
                            <?php if ( $item['image_url'] ) : ?>
                                <img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>">
                            <?php else : ?>
                                <div class="rm-product-img-placeholder"></div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="rm-product-info">
                        <h4 class="rm-product-title" title="<?php echo esc_attr( $item['title'] ); ?>">
                            <a class="rm-product-card-detail-link" href="<?php echo esc_url( $detail_url ); ?>">
                                <?php echo esc_html( $item['title'] ); ?>
                            </a>
                        </h4>
                        <?php if ( $has_variations ) : ?>
                            <div class="rm-product-card-variation-wrap">
                                <label class="screen-reader-text" for="rm-variation-select-<?php echo esc_attr( (string) $item['id'] ); ?>"><?php esc_html_e( 'Variation', 'reseller-management' ); ?></label>
                                <select id="rm-variation-select-<?php echo esc_attr( (string) $item['id'] ); ?>" class="rm-product-card-variation-select" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product title */ __( 'Variation for %s', 'reseller-management' ), $item['title'] ) ); ?>">
                                    <?php foreach ( $item['variations'] as $vrow ) : ?>
                                        <option
                                            value="<?php echo esc_attr( (string) $vrow['id'] ); ?>"
                                            data-regular="<?php echo esc_attr( (string) ( $vrow['regular'] ? $vrow['regular'] : '0' ) ); ?>"
                                            data-recommended="<?php echo esc_attr( (string) ( $vrow['recommended'] ? $vrow['recommended'] : '0' ) ); ?>"
                                            data-copy="<?php echo esc_attr( $vrow['copy_text'] ); ?>"
                                            data-image="<?php echo esc_attr( $vrow['image_url'] ); ?>"
                                            data-detail-url="<?php echo esc_url( add_query_arg( 'product_id', $vrow['id'] ) ); ?>"
                                        ><?php echo esc_html( $vrow['label'] ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
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

    <div class="rm-products-pagination rm-pagination" aria-label="<?php esc_attr_e( 'Product list pages', 'reseller-management' ); ?>"></div>
</div>
