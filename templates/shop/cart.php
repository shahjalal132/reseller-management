<?php
/**
 * Shop cart (Mohasagor-style).
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$checkout_url = trailingslashit( $shop_url ) . 'checkout/';
$subtotal     = empty( $cart ) ? 0.0 : (float) $shop->get_cart_total( $cart );
?>
<section class="rm-bag">
	<?php if ( empty( $cart ) ) : ?>
		<div class="rm-chk-card rm-bag-empty-card">
			<div class="rm-bag-empty-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="56" height="56" fill="currentColor"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0020 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
			</div>
			<h1 class="rm-chk-title"><?php esc_html_e( 'Your Bag is Empty', 'reseller-management' ); ?></h1>
			<p class="rm-bag-empty-text"><?php esc_html_e( 'Looks like you haven’t added anything yet.', 'reseller-management' ); ?></p>
			<a class="rm-bag-continue" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Continue Shopping', 'reseller-management' ); ?></a>
		</div>
	<?php else : ?>
		<div class="rm-chk-card rm-bag-card">
			<h1 class="rm-chk-title"><?php esc_html_e( 'Your Bag', 'reseller-management' ); ?></h1>

			<div class="rm-chk-table-wrap">
				<table class="rm-chk-table rm-bag-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'reseller-management' ); ?></th>
							<th><?php esc_html_e( 'Quantity', 'reseller-management' ); ?></th>
							<th><?php esc_html_e( 'Price', 'reseller-management' ); ?></th>
							<th><?php esc_html_e( 'Total Price', 'reseller-management' ); ?></th>
							<th><?php esc_html_e( 'Action', 'reseller-management' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $cart as $item ) : ?>
							<?php $line_total = (float) $item['price'] * (int) $item['quantity']; ?>
							<tr data-product-id="<?php echo esc_attr( $item['product_id'] ); ?>">
								<td class="rm-chk-product" data-label="<?php esc_attr_e( 'Product', 'reseller-management' ); ?>">
									<?php if ( ! empty( $item['image'] ) ) : ?>
										<img src="<?php echo esc_url( $item['image'] ); ?>" alt="" width="48" height="48">
									<?php endif; ?>
									<span><?php echo esc_html( $item['name'] ); ?></span>
								</td>
								<td class="rm-chk-td-qty" data-label="<?php esc_attr_e( 'Quantity', 'reseller-management' ); ?>">
									<div class="rm-chk-qty">
										<button type="button" class="rm-chk-qty-btn" data-dir="-1" data-product-id="<?php echo esc_attr( $item['product_id'] ); ?>" aria-label="<?php esc_attr_e( 'Decrease', 'reseller-management' ); ?>">−</button>
										<input type="number" class="rm-shop-cart-qty" min="1" value="<?php echo esc_attr( $item['quantity'] ); ?>" data-product-id="<?php echo esc_attr( $item['product_id'] ); ?>" data-price="<?php echo esc_attr( (string) $item['price'] ); ?>" readonly>
										<button type="button" class="rm-chk-qty-btn" data-dir="1" data-product-id="<?php echo esc_attr( $item['product_id'] ); ?>" aria-label="<?php esc_attr_e( 'Increase', 'reseller-management' ); ?>">+</button>
									</div>
								</td>
								<td class="rm-chk-unit" data-label="<?php esc_attr_e( 'Price', 'reseller-management' ); ?>">৳ <?php echo esc_html( number_format( (float) $item['price'], 0 ) ); ?></td>
								<td class="rm-chk-line-total" data-label="<?php esc_attr_e( 'Total Price', 'reseller-management' ); ?>">৳ <?php echo esc_html( number_format( $line_total, 0 ) ); ?></td>
								<td class="rm-chk-td-action" data-label="<?php esc_attr_e( 'Action', 'reseller-management' ); ?>">
									<button type="button" class="rm-chk-remove rm-shop-remove-item" data-product-id="<?php echo esc_attr( $item['product_id'] ); ?>" aria-label="<?php esc_attr_e( 'Remove', 'reseller-management' ); ?>">
										<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="rm-chk-totals">
				<div class="rm-chk-total-row rm-chk-payable">
					<span><?php esc_html_e( 'Cart Amount', 'reseller-management' ); ?></span>
					<strong>৳ <span class="rm-shop-cart-total-value"><?php echo esc_html( number_format( $subtotal, 0 ) ); ?></span></strong>
				</div>
			</div>

			<div class="rm-bag-actions">
				<a class="rm-bag-continue rm-bag-continue--ghost" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Continue Shopping', 'reseller-management' ); ?></a>
				<a class="rm-bag-checkout" href="<?php echo esc_url( $checkout_url ); ?>"><?php esc_html_e( 'Place Order', 'reseller-management' ); ?></a>
			</div>
		</div>
	<?php endif; ?>
</section>
