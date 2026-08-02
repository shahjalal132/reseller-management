<?php
/**
 * Shop thank-you / order confirmation (Mohasagor-style).
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order    = $order_id ? wc_get_order( $order_id ) : false;

// Soft check: order belongs to this shop reseller when present.
if ( $order ) {
	$assigned = (int) $order->get_meta( '_assigned_reseller_id' );
	if ( $assigned && (int) $reseller_id !== $assigned ) {
		$order = false;
	}
}

$shop_name = $business_name;
$call_phone = $phone;
?>
<section class="rm-thanks">
	<div class="rm-thanks-card">
		<div class="rm-thanks-icon" aria-hidden="true">
			<svg viewBox="0 0 52 52" width="72" height="72">
				<circle cx="26" cy="26" r="25" fill="#22c55e"/>
				<path d="M14 27l8 8 16-18" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</div>

		<h1 class="rm-thanks-title">
			“ <?php echo esc_html( $shop_name ); ?> ” <?php esc_html_e( 'এ অর্ডার করার জন্য আপনাকে ধন্যবাদ।', 'reseller-management' ); ?>
		</h1>

		<?php if ( $order ) : ?>
			<p class="rm-thanks-order">
				<?php
				printf(
					/* translators: %d: order id */
					esc_html__( 'Order #%d', 'reseller-management' ),
					(int) $order->get_id()
				);
				?>
			</p>
		<?php endif; ?>

		<p class="rm-thanks-text">
			<?php esc_html_e( 'আসসালামু আলাইকুম, আপনার অর্ডারটি কনফার্ম করা হয়েছে।', 'reseller-management' ); ?>
			<?php if ( $call_phone ) : ?>
				<?php esc_html_e( 'যেকোন প্রয়োজনে-', 'reseller-management' ); ?>
				<a class="rm-thanks-phone" href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $call_phone ) ); ?>"><?php echo esc_html( $call_phone ); ?></a>
			<?php endif; ?>
		</p>

		<p class="rm-thanks-note">
			<?php esc_html_e( 'দয়া করে প্রোডাক্টটি রিসিভ করার সময় চেক করে নিবেন।', 'reseller-management' ); ?>
		</p>

		<a class="rm-thanks-btn" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Continue Shopping', 'reseller-management' ); ?></a>
	</div>
</section>
