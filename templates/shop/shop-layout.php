<?php
/**
 * Public My Shop layout (Mohasagor-style).
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

use BOILERPLATE\Inc\Reseller_Helper;
use BOILERPLATE\Inc\Reseller_Shop;

$shop        = Reseller_Shop::get_instance();
$slug        = sanitize_title( (string) get_query_var( 'rm_shop_slug' ) );
$reseller_id = $shop->resolve_shop_reseller( $slug );

if ( ! $reseller_id ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	$not_found = PLUGIN_BASE_PATH . '/templates/404-layout.php';
	if ( file_exists( $not_found ) ) {
		include $not_found;
	} else {
		wp_die( esc_html__( 'Shop not found.', 'reseller-management' ), esc_html__( 'Not Found', 'reseller-management' ), [ 'response' => 404 ] );
	}
	return;
}

$product_id    = absint( get_query_var( 'rm_shop_product' ) );
$category_slug = sanitize_title( (string) get_query_var( 'rm_shop_category' ) );
$view          = sanitize_key( (string) get_query_var( 'rm_shop_view' ) );
$search        = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

$business_name = (string) get_user_meta( $reseller_id, '_reseller_business_name', true );
if ( ! $business_name ) {
	$user          = get_userdata( $reseller_id );
	$business_name = $user ? $user->display_name : __( 'Shop', 'reseller-management' );
}

$phone       = (string) get_user_meta( $reseller_id, '_reseller_phone', true );
$shop_url    = Reseller_Helper::get_shop_url( $reseller_id );
$brand_color = Reseller_Helper::get_shop_brand_color( $reseller_id );
$logo_url    = Reseller_Helper::get_shop_logo_url( $reseller_id, 'full' );
$cart        = $shop->get_cart( $reseller_id );
$cart_count = $shop->get_cart_count( $cart );
$cart_total = $shop->get_cart_total( $cart );
$cats_tree  = $shop->get_shop_categories_tree( $reseller_id );
$nav_cats   = $cats_tree['parents'];
$login_url  = wp_login_url( $shop_url );

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $business_name ); ?> — <?php esc_html_e( 'Online Shopping In Bangladesh', 'reseller-management' ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<style id="rm-shop-brand-vars">
		body.rm-shop-moha {
			--rm-moha-orange: <?php echo esc_html( $brand_color ); ?>;
			--rm-moha-orange-dark: color-mix(in srgb, <?php echo esc_html( $brand_color ); ?> 82%, #000);
			--rm-moha-orange-soft: color-mix(in srgb, <?php echo esc_html( $brand_color ); ?> 14%, #fff);
		}
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'rm-shop-body rm-shop-moha' ); ?>>
<?php wp_body_open(); ?>

<div class="rm-moha-utility">
	<div class="rm-moha-utility-inner">
		<a href="<?php echo esc_url( trailingslashit( $shop_url ) . 'cart/' ); ?>"><?php esc_html_e( 'Order Tracking', 'reseller-management' ); ?></a>
		<span class="rm-moha-utility-sep" aria-hidden="true">|</span>
		<a href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Login', 'reseller-management' ); ?></a>
		<?php if ( $phone ) : ?>
			<span class="rm-moha-utility-sep" aria-hidden="true">|</span>
			<a class="rm-moha-utility-call" href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $phone ) ); ?>">
				<?php esc_html_e( 'Call Us Now', 'reseller-management' ); ?>
				<strong><?php echo esc_html( $phone ); ?></strong>
			</a>
		<?php else : ?>
			<span class="rm-moha-utility-sep" aria-hidden="true">|</span>
			<span class="rm-moha-utility-call"><?php esc_html_e( 'Call Us Now', 'reseller-management' ); ?></span>
		<?php endif; ?>
	</div>
</div>

<header class="rm-moha-top">
	<div class="rm-moha-top-inner">
		<a class="rm-moha-logo <?php echo $logo_url ? 'has-image' : ''; ?>" href="<?php echo esc_url( $shop_url ); ?>">
			<?php if ( $logo_url ) : ?>
				<img class="rm-moha-logo-img" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $business_name ); ?>">
			<?php else : ?>
				<span class="rm-moha-logo-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2h2V8h4v2h2V8h2v12z"/></svg>
				</span>
				<span class="rm-moha-logo-text"><?php echo esc_html( $business_name ); ?></span>
			<?php endif; ?>
		</a>

		<form class="rm-moha-search" method="get" action="<?php echo esc_url( $shop_url ); ?>">
			<input type="search" name="q" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Looking for something?', 'reseller-management' ); ?>">
			<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'reseller-management' ); ?>">
				<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
			</button>
		</form>

		<div class="rm-moha-top-actions">
			<a class="rm-moha-cart" href="<?php echo esc_url( trailingslashit( $shop_url ) . 'cart/' ); ?>">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0020 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
				<span class="rm-moha-cart-meta">
					<small><?php esc_html_e( 'Cart amount', 'reseller-management' ); ?></small>
					<strong>৳ <span class="rm-shop-cart-total-value"><?php echo esc_html( number_format( $cart_total, 0 ) ); ?></span></strong>
				</span>
				<span class="rm-shop-cart-count"><?php echo esc_html( (string) $cart_count ); ?></span>
			</a>
		</div>
	</div>
</header>

<?php if ( ! empty( $nav_cats ) ) : ?>
	<nav class="rm-moha-nav">
		<div class="rm-moha-nav-inner">
			<?php foreach ( $nav_cats as $term ) : ?>
				<a href="<?php echo esc_url( trailingslashit( $shop_url ) . 'category/' . $term->slug . '/' ); ?>" class="<?php echo $category_slug === $term->slug ? 'is-active' : ''; ?>">
					<?php echo esc_html( $term->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</nav>
<?php endif; ?>

<main class="rm-shop-main rm-moha-main">
	<?php if ( 'checkout' === $view ) : ?>
		<?php include PLUGIN_BASE_PATH . '/templates/shop/checkout.php'; ?>
	<?php elseif ( 'cart' === $view ) : ?>
		<?php include PLUGIN_BASE_PATH . '/templates/shop/cart.php'; ?>
	<?php elseif ( 'thank-you' === $view ) : ?>
		<?php include PLUGIN_BASE_PATH . '/templates/shop/thank-you.php'; ?>
	<?php elseif ( $product_id ) : ?>
		<?php include PLUGIN_BASE_PATH . '/templates/shop/product.php'; ?>
	<?php else : ?>
		<?php include PLUGIN_BASE_PATH . '/templates/shop/catalog.php'; ?>
	<?php endif; ?>
</main>

<a class="rm-spd-float-cart" href="<?php echo esc_url( trailingslashit( $shop_url ) . 'cart/' ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'reseller-management' ); ?>">
	<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0020 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
	<span class="rm-spd-float-count"><?php echo esc_html( (string) $cart_count ); ?> <?php esc_html_e( 'items', 'reseller-management' ); ?></span>
</a>

<footer class="rm-moha-footer">
	<div class="rm-moha-footer-inner">
		<div class="rm-moha-footer-col">
			<div class="rm-moha-footer-brand">
				<?php if ( $logo_url ) : ?>
					<img class="rm-moha-footer-logo-img" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $business_name ); ?>">
				<?php else : ?>
					<span class="rm-moha-logo-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2h2V8h4v2h2V8h2v12z"/></svg>
					</span>
				<?php endif; ?>
				<strong><?php echo esc_html( $business_name ); ?></strong>
			</div>
			<?php if ( $phone ) : ?>
				<p><?php echo esc_html( $phone ); ?></p>
			<?php endif; ?>
		</div>
		<div class="rm-moha-footer-col">
			<h4><?php esc_html_e( 'Quick Links', 'reseller-management' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Home Page', 'reseller-management' ); ?></a></li>
				<li><a href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'User Login', 'reseller-management' ); ?></a></li>
				<li><a href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'User Register', 'reseller-management' ); ?></a></li>
			</ul>
		</div>
		<div class="rm-moha-footer-col">
			<h4><?php esc_html_e( 'Information', 'reseller-management' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( trailingslashit( $shop_url ) . 'cart/' ); ?>"><?php esc_html_e( 'Cart', 'reseller-management' ); ?></a></li>
			</ul>
		</div>
		<div class="rm-moha-footer-col">
			<h4><?php esc_html_e( 'Follow Us', 'reseller-management' ); ?></h4>
			<?php
			$fb = (string) get_user_meta( $reseller_id, '_reseller_fb_url', true );
			if ( $fb ) :
				?>
				<p><a href="<?php echo esc_url( $fb ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Facebook', 'reseller-management' ); ?></a></p>
			<?php endif; ?>
		</div>
	</div>
	<div class="rm-moha-footer-bar">
		<span><?php echo esc_html( $business_name ); ?></span>
		<span><?php esc_html_e( 'Online Shopping In Bangladesh', 'reseller-management' ); ?></span>
	</div>
</footer>

<?php if ( $phone ) : ?>
	<a class="rm-moha-whatsapp" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D+/', '', $phone ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
		<svg viewBox="0 0 24 24" width="28" height="28" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .004 5.408 0 12.044c0 2.123.555 4.191 1.613 6.011L0 24l6.117-1.605a11.845 11.845 0 005.932 1.577h.005c6.631 0 12.046-5.408 12.05-12.044a11.813 11.813 0 00-3.592-8.514z"/></svg>
	</a>
<?php endif; ?>

<nav class="rm-moha-mobile-bar" aria-label="<?php esc_attr_e( 'Mobile navigation', 'reseller-management' ); ?>">
	<a href="<?php echo esc_url( $shop_url ); ?>" class="<?php echo ( ! $view && ! $product_id && ! $category_slug && ! $search ) ? 'is-active' : ''; ?>">
		<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
		<span><?php esc_html_e( 'Home', 'reseller-management' ); ?></span>
	</a>
	<a href="<?php echo esc_url( $shop_url . '#rm-moha-cats' ); ?>">
		<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M4 8h4V4H4v4zm6 12h4v-4h-4v4zm-6 0h4v-4H4v4zm0-6h4v-4H4v4zm6 0h4v-4h-4v4zm6-10v4h4V4h-4zm-6 4h4V4h-4v4zm6 6h4v-4h-4v4zm0 6h4v-4h-4v4z"/></svg>
		<span><?php esc_html_e( 'Category', 'reseller-management' ); ?></span>
	</a>
	<a href="<?php echo esc_url( trailingslashit( $shop_url ) . 'cart/' ); ?>" class="<?php echo ( 'cart' === $view || 'checkout' === $view ) ? 'is-active' : ''; ?>">
		<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0020 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
		<span><?php esc_html_e( 'BAG', 'reseller-management' ); ?></span>
		<?php if ( $cart_count > 0 ) : ?>
			<em class="rm-moha-mobile-bag-count"><?php echo esc_html( (string) $cart_count ); ?></em>
		<?php endif; ?>
	</a>
	<?php if ( $phone ) : ?>
		<a href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $phone ) ); ?>">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
			<span><?php esc_html_e( 'Call', 'reseller-management' ); ?></span>
		</a>
	<?php else : ?>
		<a href="<?php echo esc_url( $login_url ); ?>">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
			<span><?php esc_html_e( 'ME', 'reseller-management' ); ?></span>
		</a>
	<?php endif; ?>
</nav>

<?php wp_footer(); ?>
</body>
</html>
