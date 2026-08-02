<?php
/**
 * Shop catalog (Mohasagor-style home + filtered views).
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a product card partial.
 *
 * @param array  $item     Product card data.
 * @param string $shop_url Shop base URL.
 */
$rm_render_card = static function ( $item ) {
	?>
	<article class="rm-moha-card">
		<a class="rm-moha-card-media" href="<?php echo esc_url( $item['permalink'] ); ?>">
			<?php if ( $item['image'] ) : ?>
				<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy">
			<?php else : ?>
				<span class="rm-moha-card-placeholder"></span>
			<?php endif; ?>
		</a>
		<div class="rm-moha-card-body">
			<a href="<?php echo esc_url( $item['permalink'] ); ?>">
				<h3><?php echo esc_html( $item['title'] ); ?></h3>
			</a>
			<p class="rm-moha-card-price">৳<?php echo esc_html( number_format( (float) $item['price'], 0 ) ); ?></p>
			<?php if ( $item['is_variable'] ) : ?>
				<a class="rm-moha-order-btn" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php esc_html_e( 'Order Now', 'reseller-management' ); ?></a>
			<?php else : ?>
				<button type="button" class="rm-moha-order-btn rm-shop-add-btn" data-product-id="<?php echo esc_attr( $item['id'] ); ?>">
					<?php esc_html_e( 'Order Now', 'reseller-management' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</article>
	<?php
};

$is_filtered = (bool) ( $category_slug || $search );

if ( $is_filtered ) :
	$products = $shop->get_shop_products( $reseller_id, $category_slug, $search, 60 );
	?>
	<section class="rm-moha-section">
		<div class="rm-moha-section-head">
			<h2>
				<?php
				if ( $search ) {
					printf(
						/* translators: %s: search query */
						esc_html__( 'Results for “%s”', 'reseller-management' ),
						esc_html( $search )
					);
				} else {
					$term = get_term_by( 'slug', $category_slug, 'product_cat' );
					echo esc_html( $term ? $term->name : $category_slug );
				}
				?>
			</h2>
		</div>
		<?php if ( empty( $products ) ) : ?>
			<p class="rm-shop-empty"><?php esc_html_e( 'No products found.', 'reseller-management' ); ?></p>
		<?php else : ?>
			<div class="rm-moha-grid">
				<?php foreach ( $products as $item ) : ?>
					<?php $rm_render_card( $item ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
	return;
endif;

$parent_cats = $cats_tree['parents'];
$child_cats  = $cats_tree['children'];
$by_parent   = [];
foreach ( $child_cats as $child ) {
	$by_parent[ (int) $child->parent ][] = $child;
}

// Circle categories: parents + some children for denser "Top Selling Category".
$circle_cats = array_slice( array_merge( $parent_cats, $child_cats ), 0, 16 );
$hot_products = $shop->get_shop_products( $reseller_id, '', '', 10 );
?>

<?php if ( ! empty( $circle_cats ) ) : ?>
	<section class="rm-moha-section rm-moha-top-cats" id="rm-moha-cats">
		<div class="rm-moha-section-head">
			<span class="rm-moha-section-bar"></span>
			<h2><?php esc_html_e( 'Top Selling Category', 'reseller-management' ); ?></h2>
		</div>
		<div class="rm-moha-cat-circles">
			<?php foreach ( $circle_cats as $term ) : ?>
				<?php
				$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
				$thumb    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
				$url      = trailingslashit( $shop_url ) . 'category/' . $term->slug . '/';
				?>
				<a class="rm-moha-cat-circle" href="<?php echo esc_url( $url ); ?>">
					<span class="rm-moha-cat-circle-img">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $term->name ); ?>">
						<?php else : ?>
							<span class="rm-moha-cat-circle-fallback"><?php echo esc_html( mb_substr( $term->name, 0, 1 ) ); ?></span>
						<?php endif; ?>
					</span>
					<span class="rm-moha-cat-circle-name"><?php echo esc_html( $term->name ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $hot_products ) ) : ?>
	<section class="rm-moha-section">
		<div class="rm-moha-section-head">
			<span class="rm-moha-section-bar"></span>
			<h2><?php esc_html_e( 'Hot Selling Products', 'reseller-management' ); ?></h2>
		</div>
		<div class="rm-moha-grid">
			<?php foreach ( $hot_products as $item ) : ?>
				<?php $rm_render_card( $item ); ?>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="rm-moha-section">
		<div class="rm-moha-section-head">
			<span class="rm-moha-section-bar"></span>
			<h2><?php esc_html_e( 'Top Selling Products', 'reseller-management' ); ?></h2>
		</div>
		<div class="rm-moha-grid">
			<?php foreach ( array_slice( $hot_products, 0, 8 ) as $item ) : ?>
				<?php $rm_render_card( $item ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php foreach ( $parent_cats as $parent ) : ?>
	<?php
	$section_products = $shop->get_shop_products( $reseller_id, $parent->slug, '', 8 );
	if ( empty( $section_products ) ) {
		continue;
	}
	$subs     = $by_parent[ (int) $parent->term_id ] ?? [];
	$view_all = trailingslashit( $shop_url ) . 'category/' . $parent->slug . '/';
	?>
	<section class="rm-moha-section">
		<div class="rm-moha-section-head rm-moha-section-head--split">
			<div>
				<span class="rm-moha-section-bar"></span>
				<h2><?php echo esc_html( $parent->name ); ?></h2>
				<?php if ( ! empty( $subs ) ) : ?>
					<div class="rm-moha-sublinks">
						<?php foreach ( array_slice( $subs, 0, 6 ) as $sub ) : ?>
							<a href="<?php echo esc_url( trailingslashit( $shop_url ) . 'category/' . $sub->slug . '/' ); ?>"><?php echo esc_html( $sub->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<a class="rm-moha-view-all" href="<?php echo esc_url( $view_all ); ?>"><?php esc_html_e( 'View All', 'reseller-management' ); ?></a>
		</div>
		<div class="rm-moha-grid">
			<?php foreach ( $section_products as $item ) : ?>
				<?php $rm_render_card( $item ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endforeach; ?>

<?php if ( empty( $hot_products ) && empty( $parent_cats ) ) : ?>
	<p class="rm-shop-empty"><?php esc_html_e( 'No products found.', 'reseller-management' ); ?></p>
<?php endif; ?>
