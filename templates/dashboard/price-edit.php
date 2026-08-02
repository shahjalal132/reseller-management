<?php
/**
 * Price Edit tab.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

use BOILERPLATE\Inc\Reseller_Helper;

$user_id = get_current_user_id();

$products = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'post_parent'    => 0,
		'posts_per_page' => 200,
		'orderby'        => 'date',
		'order'          => 'DESC',
	]
);

$rows = [];

foreach ( $products as $product_post ) {
	$product = wc_get_product( $product_post->ID );
	if ( ! $product ) {
		continue;
	}

	$targets = [];
	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_children() as $child_id ) {
			$variation = wc_get_product( $child_id );
			if ( $variation && $variation->exists() ) {
				$targets[] = $variation;
			}
		}
	} else {
		$targets[] = $product;
	}

	foreach ( $targets as $target ) {
		$base         = (float) $target->get_price();
		$recommended  = Reseller_Helper::get_product_recommended_price( $target );
		$selling      = Reseller_Helper::get_reseller_selling_price( $user_id, $target );
		$image_id     = (int) $target->get_image_id();
		if ( ! $image_id ) {
			$image_id = (int) $product->get_image_id();
		}

		$rows[] = [
			'id'          => $target->get_id(),
			'title'       => $target->get_name(),
			'base'        => $base,
			'recommended' => $recommended,
			'selling'     => $selling,
			'profit'      => round( $selling - $base, 2 ),
			'image'       => $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '',
			'sku'         => $target->get_sku(),
		];
	}
}
?>
<div class="rm-card rm-price-edit-card">
	<h3><?php esc_html_e( 'Price Edit', 'reseller-management' ); ?></h3>
	<p class="rm-muted"><?php esc_html_e( 'Set your selling price for each product. Profit is selling price minus base cost. Selling price cannot be lower than base.', 'reseller-management' ); ?></p>

	<div class="rm-price-edit-filter">
		<input type="search" class="rm-price-edit-search" placeholder="<?php esc_attr_e( 'Search product name or SKU…', 'reseller-management' ); ?>">
	</div>

	<div class="rm-table-wrap">
		<table class="rm-table rm-price-edit-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Product', 'reseller-management' ); ?></th>
					<th><?php esc_html_e( 'Base', 'reseller-management' ); ?></th>
					<th><?php esc_html_e( 'Recommended', 'reseller-management' ); ?></th>
					<th><?php esc_html_e( 'Selling Price', 'reseller-management' ); ?></th>
					<th><?php esc_html_e( 'Profit', 'reseller-management' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $rows ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'No products found.', 'reseller-management' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $rows as $row ) : ?>
						<tr class="rm-price-edit-row" data-search="<?php echo esc_attr( strtolower( $row['title'] . ' ' . $row['sku'] ) ); ?>">
							<td>
								<div class="rm-price-edit-product">
									<?php if ( $row['image'] ) : ?>
										<img src="<?php echo esc_url( $row['image'] ); ?>" alt="" width="40" height="40">
									<?php endif; ?>
									<span>
										<?php echo esc_html( $row['title'] ); ?>
										<?php if ( $row['sku'] ) : ?>
											<small><?php echo esc_html( $row['sku'] ); ?></small>
										<?php endif; ?>
									</span>
								</div>
							</td>
							<td class="rm-price-base" data-base="<?php echo esc_attr( $row['base'] ); ?>"><?php echo esc_html( number_format( $row['base'], 2 ) ); ?></td>
							<td><?php echo esc_html( number_format( $row['recommended'], 2 ) ); ?></td>
							<td>
								<input type="number" class="rm-price-selling" min="<?php echo esc_attr( $row['base'] ); ?>" step="0.01" value="<?php echo esc_attr( $row['selling'] ); ?>" data-product-id="<?php echo esc_attr( $row['id'] ); ?>">
							</td>
							<td class="rm-price-profit"><?php echo esc_html( number_format( $row['profit'], 2 ) ); ?></td>
							<td>
								<button type="button" class="rm-button rm-button-sm rm-save-price" data-product-id="<?php echo esc_attr( $row['id'] ); ?>"><?php esc_html_e( 'Save', 'reseller-management' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<div class="rm-form-response rm-price-edit-response" aria-live="polite"></div>
</div>
