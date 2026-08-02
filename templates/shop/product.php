<?php
/**
 * Shop single product (Mohasagor-style).
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$product   = wc_get_product( $product_id );
$parent_id = $product && $product->get_parent_id() ? $product->get_parent_id() : $product_id;

if ( ! $product || 'publish' !== get_post_status( $parent_id ) || ! \BOILERPLATE\Inc\Reseller_Helper::is_product_visible_in_shop( $reseller_id, $parent_id ) ) {
	echo '<p class="rm-shop-empty">' . esc_html__( 'Product not found.', 'reseller-management' ) . '</p>';
	return;
}

if ( $product->get_parent_id() ) {
	$product = wc_get_product( $product->get_parent_id() );
}

$card = $shop->format_product_card( $reseller_id, $product );

$gallery_ids = $product->get_gallery_image_ids();
$images      = [];
if ( $card['image'] ) {
	$images[] = [
		'full' => wp_get_attachment_image_url( (int) $product->get_image_id(), 'large' ) ?: $card['image'],
		'thumb'=> $card['image'],
	];
}
foreach ( $gallery_ids as $gid ) {
	$full  = wp_get_attachment_image_url( (int) $gid, 'large' );
	$thumb = wp_get_attachment_image_url( (int) $gid, 'thumbnail' );
	if ( $full ) {
		$images[] = [
			'full'  => $full,
			'thumb' => $thumb ?: $full,
		];
	}
}
if ( empty( $images ) ) {
	$images[] = [
		'full'  => '',
		'thumb' => '',
	];
}

$desc_html = $product->get_description();
if ( ! $desc_html ) {
	$desc_html = $product->get_short_description();
}

$related = [];
$term_ids = wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'ids' ] );
if ( ! is_wp_error( $term_ids ) && ! empty( $term_ids ) ) {
	$related_posts = get_posts(
		[
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'post_parent'    => 0,
			'posts_per_page' => 8,
			'post__not_in'   => [ $product->get_id() ],
			'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $term_ids,
				],
			],
		]
	);
	foreach ( $related_posts as $rp ) {
		if ( ! \BOILERPLATE\Inc\Reseller_Helper::is_product_visible_in_shop( $reseller_id, $rp->ID ) ) {
			continue;
		}
		$rp_product = wc_get_product( $rp->ID );
		if ( $rp_product ) {
			$related[] = $shop->format_product_card( $reseller_id, $rp_product );
		}
	}
}

$checkout_url = trailingslashit( $shop_url ) . 'checkout/';
$cart_url     = trailingslashit( $shop_url ) . 'cart/';
?>
<article class="rm-spd" data-product-id="<?php echo esc_attr( $card['id'] ); ?>">
	<div class="rm-spd-main">
		<div class="rm-spd-gallery">
			<div class="rm-spd-main-image">
				<?php if ( ! empty( $images[0]['full'] ) ) : ?>
					<img src="<?php echo esc_url( $images[0]['full'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>" class="rm-spd-zoom-img" id="rm-spd-main-img">
					<span class="rm-spd-zoom-hint"><?php esc_html_e( 'Hover to zoom', 'reseller-management' ); ?></span>
				<?php else : ?>
					<span class="rm-moha-card-placeholder rm-spd-placeholder"></span>
				<?php endif; ?>
			</div>
			<?php if ( count( $images ) > 1 ) : ?>
				<div class="rm-spd-thumbs">
					<?php foreach ( $images as $i => $img ) : ?>
						<button type="button" class="rm-spd-thumb <?php echo 0 === $i ? 'is-active' : ''; ?>" data-full="<?php echo esc_url( $img['full'] ); ?>">
							<img src="<?php echo esc_url( $img['thumb'] ); ?>" alt="">
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="rm-spd-info">
			<h1 class="rm-spd-title"><?php echo esc_html( $card['title'] ); ?></h1>

			<div class="rm-spd-meta">
				<?php if ( $card['sku'] ) : ?>
					<p class="rm-spd-code"><?php esc_html_e( 'Code:', 'reseller-management' ); ?> <strong><?php echo esc_html( $card['sku'] ); ?></strong></p>
				<?php endif; ?>
				<p class="rm-spd-price">
					<?php esc_html_e( 'Price:', 'reseller-management' ); ?>
					<strong>৳ <span class="rm-shop-detail-price-val"><?php echo esc_html( number_format( (float) $card['price'], 0 ) ); ?></span></strong>
				</p>
			</div>

			<?php if ( ! empty( $card['variations'] ) ) : ?>
				<label class="rm-spd-variation">
					<span><?php esc_html_e( 'Option', 'reseller-management' ); ?></span>
					<select class="rm-shop-variation-select">
						<option value=""><?php esc_html_e( 'Select…', 'reseller-management' ); ?></option>
						<?php foreach ( $card['variations'] as $variation ) : ?>
							<?php
							$labels = [];
							foreach ( $variation['attributes'] as $attr_val ) {
								$labels[] = is_string( $attr_val ) ? $attr_val : '';
							}
							$label = implode( ' / ', array_filter( $labels ) ) ?: ( '#' . $variation['id'] );
							?>
							<option value="<?php echo esc_attr( $variation['id'] ); ?>" data-price="<?php echo esc_attr( $variation['price'] ); ?>">
								<?php echo esc_html( $label . ' — ৳ ' . number_format( (float) $variation['price'], 0 ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php endif; ?>

			<div class="rm-spd-actions">
				<div class="rm-spd-qty">
					<button type="button" class="rm-spd-qty-btn" data-dir="-1" aria-label="<?php esc_attr_e( 'Decrease', 'reseller-management' ); ?>">−</button>
					<input type="number" class="rm-shop-qty" min="1" value="1" readonly>
					<button type="button" class="rm-spd-qty-btn" data-dir="1" aria-label="<?php esc_attr_e( 'Increase', 'reseller-management' ); ?>">+</button>
				</div>

				<button type="button" class="rm-spd-order-now rm-shop-add-btn" data-product-id="<?php echo esc_attr( $card['id'] ); ?>" data-variable="<?php echo $card['is_variable'] ? '1' : '0'; ?>" data-redirect="checkout">
					<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
					<?php esc_html_e( 'Order Now', 'reseller-management' ); ?>
				</button>

				<div class="rm-spd-secondary-actions">
					<button type="button" class="rm-spd-secondary rm-shop-add-btn" data-product-id="<?php echo esc_attr( $card['id'] ); ?>" data-variable="<?php echo $card['is_variable'] ? '1' : '0'; ?>">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0020 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
						<?php esc_html_e( 'Add To Cart', 'reseller-management' ); ?>
					</button>
					<button type="button" class="rm-spd-secondary rm-spd-wishlist">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
						<?php esc_html_e( 'Add to Wishlist', 'reseller-management' ); ?>
					</button>
				</div>
			</div>

			<div class="rm-spd-info-box">
				<p><span class="rm-spd-check">✓</span> <?php esc_html_e( 'Delivery within 1–3 days inside Dhaka city & 2–5 days anywhere in Bangladesh.', 'reseller-management' ); ?></p>
				<p><span class="rm-spd-check">👍</span> <?php esc_html_e( '100% money back guarantee · Quality product · Trusted service.', 'reseller-management' ); ?></p>
				<p><span class="rm-spd-check">✓</span> <?php esc_html_e( 'Cash on delivery available.', 'reseller-management' ); ?></p>
				<p><span class="rm-spd-truck">🚚</span> <?php esc_html_e( 'Delivery charge: inside Dhaka ৳60 · outside Dhaka ৳120 (approx).', 'reseller-management' ); ?></p>
			</div>

			<?php if ( ! empty( $phone ) ) : ?>
				<div class="rm-spd-call-box">
					<?php esc_html_e( 'Have question about this product? please call', 'reseller-management' ); ?>
					<a href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="rm-spd-tabs">
		<div class="rm-spd-tab-nav" role="tablist">
			<button type="button" class="rm-spd-tab is-active" data-tab="desc" role="tab"><?php esc_html_e( 'DESCRIPTION', 'reseller-management' ); ?></button>
			<button type="button" class="rm-spd-tab" data-tab="buy" role="tab"><?php esc_html_e( 'HOW TO BUY', 'reseller-management' ); ?></button>
			<button type="button" class="rm-spd-tab" data-tab="return" role="tab"><?php esc_html_e( 'RETURN POLICY', 'reseller-management' ); ?></button>
		</div>
		<div class="rm-spd-tab-panels">
			<div class="rm-spd-panel is-active" data-panel="desc" role="tabpanel">
				<?php
				if ( $desc_html ) {
					echo wp_kses_post( wpautop( $desc_html ) );
				} else {
					echo '<p>' . esc_html__( 'No description available.', 'reseller-management' ) . '</p>';
				}
				?>
			</div>
			<div class="rm-spd-panel" data-panel="buy" role="tabpanel" hidden>
				<ol class="rm-spd-howto">
					<li><?php esc_html_e( 'Select quantity (and variation if needed).', 'reseller-management' ); ?></li>
					<li><?php esc_html_e( 'Click Order Now or Add To Cart.', 'reseller-management' ); ?></li>
					<li><?php esc_html_e( 'Enter your name, phone and delivery address.', 'reseller-management' ); ?></li>
					<li><?php esc_html_e( 'Confirm — pay cash on delivery when you receive the product.', 'reseller-management' ); ?></li>
				</ol>
			</div>
			<div class="rm-spd-panel" data-panel="return" role="tabpanel" hidden>
				<p><?php esc_html_e( 'If you receive a damaged or wrong product, please contact us within 24 hours of delivery with photos. Eligible items may be replaced or refunded as per shop policy.', 'reseller-management' ); ?></p>
			</div>
		</div>
	</div>
</article>

<?php if ( ! empty( $related ) ) : ?>
	<section class="rm-moha-section rm-spd-related">
		<div class="rm-moha-section-head">
			<span class="rm-moha-section-bar"></span>
			<h2><?php esc_html_e( 'Related Products', 'reseller-management' ); ?></h2>
		</div>
		<div class="rm-moha-grid">
			<?php foreach ( $related as $item ) : ?>
				<article class="rm-moha-card">
					<a class="rm-moha-card-media" href="<?php echo esc_url( $item['permalink'] ); ?>">
						<?php if ( $item['image'] ) : ?>
							<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy">
						<?php endif; ?>
					</a>
					<div class="rm-moha-card-body">
						<a href="<?php echo esc_url( $item['permalink'] ); ?>"><h3><?php echo esc_html( $item['title'] ); ?></h3></a>
						<p class="rm-moha-card-price">৳<?php echo esc_html( number_format( (float) $item['price'], 0 ) ); ?></p>
						<?php if ( $item['is_variable'] ) : ?>
							<a class="rm-moha-order-btn" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php esc_html_e( 'Order Now', 'reseller-management' ); ?></a>
						<?php else : ?>
							<button type="button" class="rm-moha-order-btn rm-shop-add-btn" data-product-id="<?php echo esc_attr( $item['id'] ); ?>"><?php esc_html_e( 'Order Now', 'reseller-management' ); ?></button>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>
