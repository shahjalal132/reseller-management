<?php
/**
 * Shop COD checkout (Mohasagor-style).
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $cart ) ) {
	echo '<p class="rm-shop-empty">' . esc_html__( 'Your cart is empty', 'reseller-management' ) . '</p>';
	echo '<a class="rm-button" href="' . esc_url( $shop_url ) . '">' . esc_html__( 'Continue shopping', 'reseller-management' ) . '</a>';
	return;
}

$subtotal = (float) $shop->get_cart_total( $cart );

$areas = \BOILERPLATE\Inc\Reseller_Helper::get_shipping_presets();
if ( empty( $areas ) ) {
	$areas = [
		[
			'title'  => __( 'Inside Dhaka', 'reseller-management' ),
			'charge' => 60,
		],
		[
			'title'  => __( 'Outside Dhaka', 'reseller-management' ),
			'charge' => 120,
		],
	];
}

$default_area   = $areas[0];
$shipping       = (float) $default_area['charge'];
$payable        = $subtotal + $shipping;
$cart_url       = trailingslashit( $shop_url ) . 'cart/';
?>
<section class="rm-chk" data-subtotal="<?php echo esc_attr( (string) $subtotal ); ?>">
	<div class="rm-chk-grid">
		<div class="rm-chk-card rm-chk-form-card">
			<h1 class="rm-chk-title"><?php esc_html_e( 'Please Order Now', 'reseller-management' ); ?></h1>

			<form id="rm-shop-checkout-form" class="rm-chk-form">
				<label class="rm-chk-field">
					<span><?php esc_html_e( 'Full Name', 'reseller-management' ); ?></span>
					<input type="text" name="customer_name" placeholder="<?php esc_attr_e( 'Your Name', 'reseller-management' ); ?>" required>
				</label>
				<label class="rm-chk-field">
					<span><?php esc_html_e( 'Mobile Number', 'reseller-management' ); ?></span>
					<input type="text" name="customer_phone" placeholder="01xxxxxxxxx" required>
				</label>
				<label class="rm-chk-field">
					<span><?php esc_html_e( 'Full Address', 'reseller-management' ); ?></span>
					<textarea name="customer_address" rows="3" placeholder="<?php esc_attr_e( 'Full Address', 'reseller-management' ); ?>" required></textarea>
				</label>
				<label class="rm-chk-field">
					<span><?php esc_html_e( 'Select Your Area', 'reseller-management' ); ?></span>
					<select name="shipping_area" id="rm-chk-area" required>
						<?php foreach ( $areas as $i => $area ) : ?>
							<option value="<?php echo esc_attr( $area['title'] ); ?>" data-charge="<?php echo esc_attr( (string) $area['charge'] ); ?>" <?php selected( 0, $i ); ?>>
								<?php echo esc_html( $area['title'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
				<input type="hidden" name="shipping_charge" id="rm-chk-shipping-charge" value="<?php echo esc_attr( (string) $shipping ); ?>">
				<input type="hidden" name="district" id="rm-chk-district" value="<?php echo esc_attr( $default_area['title'] ); ?>">
				<input type="hidden" name="thana" value="">
				<input type="hidden" name="order_notes" value="">

				<button type="submit" class="rm-chk-place" id="rm-shop-place-order"><?php esc_html_e( 'Place Order', 'reseller-management' ); ?></button>
				<div class="rm-form-response" aria-live="polite"></div>
			</form>
		</div>

		<div class="rm-chk-card rm-chk-summary-card">
			<h2 class="rm-chk-title"><?php esc_html_e( 'Order Summary', 'reseller-management' ); ?></h2>

			<div class="rm-chk-table-wrap">
				<table class="rm-chk-table">
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
							<?php
							$line_total = (float) $item['price'] * (int) $item['quantity'];
							?>
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
				<div class="rm-chk-total-row">
					<span><?php esc_html_e( 'Sub Total', 'reseller-management' ); ?></span>
					<strong>৳ <span id="rm-chk-subtotal"><?php echo esc_html( number_format( $subtotal, 0 ) ); ?></span></strong>
				</div>
				<div class="rm-chk-total-row">
					<span><?php esc_html_e( 'Shipping Charge', 'reseller-management' ); ?></span>
					<strong>৳ <span id="rm-chk-shipping"><?php echo esc_html( number_format( $shipping, 0 ) ); ?></span></strong>
				</div>
				<div class="rm-chk-total-row rm-chk-payable">
					<span><?php esc_html_e( 'Payable Amount', 'reseller-management' ); ?></span>
					<strong>৳ <span id="rm-chk-payable"><?php echo esc_html( number_format( $payable, 0 ) ); ?></span></strong>
				</div>
			</div>

			<div class="rm-chk-coupon">
				<label for="rm-chk-coupon-input"><?php esc_html_e( 'Apply Coupon Here', 'reseller-management' ); ?></label>
				<div class="rm-chk-coupon-row">
					<input type="text" id="rm-chk-coupon-input" placeholder="<?php esc_attr_e( 'Coupon code', 'reseller-management' ); ?>">
					<button type="button" id="rm-chk-coupon-apply"><?php esc_html_e( 'Apply', 'reseller-management' ); ?></button>
				</div>
				<p class="rm-chk-coupon-msg" aria-live="polite"></p>
			</div>
		</div>
	</div>
</section>
