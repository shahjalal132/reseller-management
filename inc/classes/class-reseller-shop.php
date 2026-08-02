<?php
/**
 * My Shop storefront, price edit, and category visibility.
 *
 * @package reseller-management
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Shop {
	use Singleton;

	const CART_COOKIE = 'rm_shop_cart';

	/**
	 * Register hooks.
	 */
	protected function __construct() {
		add_action( 'init', [ $this, 'register_rewrites' ] );
		add_filter( 'query_vars', [ $this, 'register_query_vars' ] );
		add_filter( 'template_include', [ $this, 'maybe_use_shop_template' ], 99 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_shop_assets' ], 20 );

		add_action( 'wp_ajax_reseller_save_product_price', [ $this, 'handle_save_product_price' ] );
		add_action( 'wp_ajax_reseller_toggle_category_status', [ $this, 'handle_toggle_category_status' ] );
		add_action( 'wp_ajax_reseller_save_shop_slug', [ $this, 'handle_save_shop_slug' ] );

		add_action( 'wp_ajax_reseller_shop_add_to_cart', [ $this, 'handle_add_to_cart' ] );
		add_action( 'wp_ajax_nopriv_reseller_shop_add_to_cart', [ $this, 'handle_add_to_cart' ] );
		add_action( 'wp_ajax_reseller_shop_update_cart', [ $this, 'handle_update_cart' ] );
		add_action( 'wp_ajax_nopriv_reseller_shop_update_cart', [ $this, 'handle_update_cart' ] );
		add_action( 'wp_ajax_reseller_shop_remove_cart_item', [ $this, 'handle_remove_cart_item' ] );
		add_action( 'wp_ajax_nopriv_reseller_shop_remove_cart_item', [ $this, 'handle_remove_cart_item' ] );
		add_action( 'wp_ajax_reseller_shop_place_order', [ $this, 'handle_place_order' ] );
		add_action( 'wp_ajax_nopriv_reseller_shop_place_order', [ $this, 'handle_place_order' ] );
	}

	/**
	 * Register rewrite rules for /shop/{slug}/...
	 *
	 * @return void
	 */
	public function register_rewrites() {
		add_rewrite_rule( '^shop/([^/]+)/product/([0-9]+)/?$', 'index.php?rm_shop_slug=$matches[1]&rm_shop_product=$matches[2]', 'top' );
		add_rewrite_rule( '^shop/([^/]+)/category/([^/]+)/?$', 'index.php?rm_shop_slug=$matches[1]&rm_shop_category=$matches[2]', 'top' );
		add_rewrite_rule( '^shop/([^/]+)/checkout/?$', 'index.php?rm_shop_slug=$matches[1]&rm_shop_view=checkout', 'top' );
		add_rewrite_rule( '^shop/([^/]+)/cart/?$', 'index.php?rm_shop_slug=$matches[1]&rm_shop_view=cart', 'top' );
		add_rewrite_rule( '^shop/([^/]+)/thank-you/?$', 'index.php?rm_shop_slug=$matches[1]&rm_shop_view=thank-you', 'top' );
		add_rewrite_rule( '^shop/([^/]+)/?$', 'index.php?rm_shop_slug=$matches[1]', 'top' );

		if ( '2' !== get_option( 'rm_shop_rewrite_flushed', '' ) ) {
			flush_rewrite_rules( false );
			update_option( 'rm_shop_rewrite_flushed', '2', false );
		}
	}

	/**
	 * Register custom query vars.
	 *
	 * @param string[] $vars Query vars.
	 *
	 * @return string[]
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'rm_shop_slug';
		$vars[] = 'rm_shop_product';
		$vars[] = 'rm_shop_category';
		$vars[] = 'rm_shop_view';

		return $vars;
	}

	/**
	 * Whether the current request is a My Shop page.
	 *
	 * @return bool
	 */
	public function is_shop_request() {
		return (bool) get_query_var( 'rm_shop_slug' );
	}

	/**
	 * Swap template for My Shop routes.
	 *
	 * @param string $template Current template.
	 *
	 * @return string
	 */
	public function maybe_use_shop_template( $template ) {
		if ( ! $this->is_shop_request() ) {
			return $template;
		}

		$shop_template = PLUGIN_BASE_PATH . '/templates/shop/shop-layout.php';
		if ( file_exists( $shop_template ) ) {
			return $shop_template;
		}

		return $template;
	}

	/**
	 * Enqueue storefront assets.
	 *
	 * @return void
	 */
	public function enqueue_shop_assets() {
		if ( ! $this->is_shop_request() ) {
			return;
		}

		wp_enqueue_style(
			'rm-shop-css',
			PLUGIN_PUBLIC_ASSETS_URL . '/css/shop-style.css',
			[ 'wpb-public-css' ],
			time(),
			'all'
		);
		Reseller_Helper::enqueue_branding_assets( 'rm-shop-css', 'public' );

		wp_enqueue_script(
			'rm-shop-js',
			PLUGIN_PUBLIC_ASSETS_URL . '/js/shop-script.js',
			[ 'jquery' ],
			time(),
			true
		);

		$slug        = sanitize_title( (string) get_query_var( 'rm_shop_slug' ) );
		$reseller_id = Reseller_Helper::get_reseller_id_by_shop_slug( $slug );
		$cart        = $this->get_cart( $reseller_id );

		wp_localize_script(
			'rm-shop-js',
			'rmShop',
			[
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'rm_shop_nonce' ),
				'shopSlug'   => $slug,
				'shopUrl'    => $reseller_id ? Reseller_Helper::get_shop_url( $reseller_id ) : '',
				'cartCount'  => $this->get_cart_count( $cart ),
				'cartTotal'  => $this->get_cart_total( $cart ),
				'i18n'       => [
					'added'     => __( 'Added to bag', 'reseller-management' ),
					'error'     => __( 'Something went wrong.', 'reseller-management' ),
					'emptyCart' => __( 'Your cart is empty', 'reseller-management' ),
				],
			]
		);
	}

	/**
	 * Resolve approved reseller for a shop slug or die/null.
	 *
	 * @param string $slug Shop slug.
	 *
	 * @return int Reseller ID or 0.
	 */
	public function resolve_shop_reseller( $slug ) {
		$reseller_id = Reseller_Helper::get_reseller_id_by_shop_slug( $slug );
		if ( ! $reseller_id || ! Reseller_Helper::is_reseller_approved( $reseller_id ) ) {
			return 0;
		}

		return $reseller_id;
	}

	/**
	 * Build product list for a reseller shop.
	 *
	 * @param int    $reseller_id   Reseller ID.
	 * @param string $category_slug Optional category slug filter.
	 * @param string $search        Optional search.
	 * @param int    $limit         Max products (default 100).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_shop_products( $reseller_id, $category_slug = '', $search = '', $limit = 100 ) {
		$args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'post_parent'    => 0,
			'posts_per_page' => max( 1, absint( $limit ) ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		$tax_query = [];
		if ( $category_slug ) {
			$tax_query[] = [
				'taxonomy'         => 'product_cat',
				'field'            => 'slug',
				'terms'            => $category_slug,
				'include_children' => true,
			];
		}

		if ( $tax_query ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		$posts    = get_posts( $args );
		$products = [];

		foreach ( $posts as $post ) {
			if ( ! Reseller_Helper::is_product_visible_in_shop( $reseller_id, $post->ID ) ) {
				continue;
			}

			$product = wc_get_product( $post->ID );
			if ( ! $product ) {
				continue;
			}

			$products[] = $this->format_product_card( $reseller_id, $product );
		}

		return $products;
	}

	/**
	 * Format product for shop card / detail.
	 *
	 * @param int         $reseller_id Reseller ID.
	 * @param \WC_Product $product     Product.
	 *
	 * @return array<string, mixed>
	 */
	public function format_product_card( $reseller_id, $product ) {
		$image_id = (int) $product->get_image_id();
		$price    = Reseller_Helper::get_reseller_selling_price( $reseller_id, $product );
		$base     = (float) $product->get_price();

		$variations = [];
		if ( $product->is_type( 'variable' ) ) {
			foreach ( $product->get_available_variations() as $variation_data ) {
				$variation = wc_get_product( $variation_data['variation_id'] );
				if ( ! $variation ) {
					continue;
				}
				$variations[] = [
					'id'         => $variation->get_id(),
					'attributes' => $variation_data['attributes'],
					'price'      => Reseller_Helper::get_reseller_selling_price( $reseller_id, $variation ),
					'base_price' => (float) $variation->get_price(),
					'image'      => wp_get_attachment_image_url( (int) $variation->get_image_id(), 'medium' ),
				];
			}
		}

		return [
			'id'          => $product->get_id(),
			'title'       => $product->get_name(),
			'price'       => $price,
			'base_price'  => $base,
			'image'       => $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '',
			'sku'         => $product->get_sku(),
			'permalink'   => trailingslashit( Reseller_Helper::get_shop_url( $reseller_id ) ) . 'product/' . $product->get_id() . '/',
			'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
			'is_variable' => $product->is_type( 'variable' ),
			'variations'  => $variations,
		];
	}

	/**
	 * Active product categories for shop nav (3-level aware; parents of active children stay useful).
	 *
	 * @param int $reseller_id Reseller ID.
	 *
	 * @return array{parents: array, children: array, grandchildren: array}
	 */
	public function get_shop_categories_tree( $reseller_id ) {
		$terms = get_terms(
			[
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
			]
		);

		$parents = $children = $grandchildren = [];

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return compact( 'parents', 'children', 'grandchildren' );
		}

		$by_id = [];
		foreach ( $terms as $term ) {
			$by_id[ $term->term_id ] = $term;
		}

		foreach ( $terms as $term ) {
			if ( ! Reseller_Helper::is_category_active( $reseller_id, $term->term_id ) ) {
				continue;
			}

			$parent_id = (int) $term->parent;
			if ( 0 === $parent_id ) {
				$parents[] = $term;
				continue;
			}

			$grandparent_id = isset( $by_id[ $parent_id ] ) ? (int) $by_id[ $parent_id ]->parent : 0;
			if ( 0 === $grandparent_id ) {
				$children[] = $term;
			} else {
				$grandchildren[] = $term;
			}
		}

		return compact( 'parents', 'children', 'grandchildren' );
	}

	/**
	 * Get cart array for reseller shop.
	 *
	 * @param int $reseller_id Reseller ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_cart( $reseller_id ) {
		$raw = isset( $_COOKIE[ self::CART_COOKIE ] ) ? wp_unslash( $_COOKIE[ self::CART_COOKIE ] ) : '';
		if ( ! $raw ) {
			return [];
		}

		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) || (int) ( $data['reseller_id'] ?? 0 ) !== (int) $reseller_id ) {
			return [];
		}

		$items = $data['items'] ?? [];
		return is_array( $items ) ? $items : [];
	}

	/**
	 * Persist cart cookie.
	 *
	 * @param int   $reseller_id Reseller ID.
	 * @param array $items       Cart items.
	 *
	 * @return void
	 */
	public function set_cart( $reseller_id, array $items ) {
		$payload = wp_json_encode(
			[
				'reseller_id' => (int) $reseller_id,
				'items'       => array_values( $items ),
			]
		);

		$expire = time() + WEEK_IN_SECONDS;
		setcookie( self::CART_COOKIE, $payload, $expire, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
		$_COOKIE[ self::CART_COOKIE ] = $payload;
	}

	/**
	 * Cart item count.
	 *
	 * @param array $cart Cart items.
	 *
	 * @return int
	 */
	public function get_cart_count( array $cart ) {
		$count = 0;
		foreach ( $cart as $item ) {
			$count += absint( $item['quantity'] ?? 0 );
		}
		return $count;
	}

	/**
	 * Cart total.
	 *
	 * @param array $cart Cart items.
	 *
	 * @return float
	 */
	public function get_cart_total( array $cart ) {
		$total = 0.0;
		foreach ( $cart as $item ) {
			$total += (float) ( $item['price'] ?? 0 ) * absint( $item['quantity'] ?? 0 );
		}
		return round( $total, 2 );
	}

	/**
	 * AJAX: save product selling price.
	 *
	 * @return void
	 */
	public function handle_save_product_price() {
		check_ajax_referer( 'rm_public_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! Reseller_Helper::is_reseller_approved( $user_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Not allowed.', 'reseller-management' ) ], 403 );
		}

		$product_id    = absint( $_POST['product_id'] ?? 0 );
		$selling_price = round( (float) ( $_POST['selling_price'] ?? 0 ), 2 );
		$product       = wc_get_product( $product_id );

		if ( ! $product ) {
			wp_send_json_error( [ 'message' => __( 'Invalid product.', 'reseller-management' ) ], 422 );
		}

		$base = (float) $product->get_price();
		if ( $selling_price < $base ) {
			wp_send_json_error(
				[
					'message' => sprintf(
						/* translators: %s: base price */
						__( 'Selling price must be at least %s.', 'reseller-management' ),
						wc_format_localized_price( $base )
					),
				],
				422
			);
		}

		$saved = Reseller_Helper::save_reseller_selling_price( $user_id, $product_id, $selling_price );
		if ( ! $saved ) {
			wp_send_json_error( [ 'message' => __( 'Could not save price.', 'reseller-management' ) ], 500 );
		}

		wp_send_json_success(
			[
				'selling_price' => $selling_price,
				'profit'        => round( $selling_price - $base, 2 ),
				'message'       => __( 'Price saved.', 'reseller-management' ),
			]
		);
	}

	/**
	 * AJAX: toggle category active status.
	 *
	 * @return void
	 */
	public function handle_toggle_category_status() {
		check_ajax_referer( 'rm_public_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! Reseller_Helper::is_reseller_approved( $user_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Not allowed.', 'reseller-management' ) ], 403 );
		}

		$term_id = absint( $_POST['term_id'] ?? 0 );
		$term    = get_term( $term_id, 'product_cat' );
		if ( ! $term || is_wp_error( $term ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid category.', 'reseller-management' ) ], 422 );
		}

		$status = Reseller_Helper::toggle_category_status( $user_id, $term_id );
		if ( false === $status ) {
			wp_send_json_error( [ 'message' => __( 'Could not update category.', 'reseller-management' ) ], 500 );
		}

		wp_send_json_success(
			[
				'status'  => (int) $status,
				'label'   => $status ? __( 'Active', 'reseller-management' ) : __( 'De-active', 'reseller-management' ),
				'message' => __( 'Category status updated.', 'reseller-management' ),
			]
		);
	}

	/**
	 * AJAX: save shop slug, brand color, and logo.
	 *
	 * @return void
	 */
	public function handle_save_shop_slug() {
		check_ajax_referer( 'rm_public_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! Reseller_Helper::is_reseller_approved( $user_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Not allowed.', 'reseller-management' ) ], 403 );
		}

		$desired = sanitize_title( wp_unslash( $_POST['shop_slug'] ?? '' ) );
		if ( ! $desired ) {
			wp_send_json_error( [ 'message' => __( 'Please enter a valid shop slug.', 'reseller-management' ) ], 422 );
		}

		$slug = Reseller_Helper::ensure_unique_shop_slug( $desired, $user_id );
		update_user_meta( $user_id, Reseller_Helper::get_shop_slug_meta_key(), $slug );

		$brand_color = Reseller_Helper::sanitize_shop_brand_color( wp_unslash( $_POST['brand_color'] ?? '' ) );
		if ( $brand_color ) {
			update_user_meta( $user_id, Reseller_Helper::get_shop_brand_color_meta_key(), $brand_color );
		} else {
			delete_user_meta( $user_id, Reseller_Helper::get_shop_brand_color_meta_key() );
			$brand_color = Reseller_Helper::get_default_shop_brand_color();
		}

		$logo_meta = Reseller_Helper::get_shop_logo_meta_key();
		$remove    = ! empty( $_POST['remove_logo'] ) && '1' === (string) wp_unslash( $_POST['remove_logo'] );

		if ( $remove ) {
			$old_logo = (int) get_user_meta( $user_id, $logo_meta, true );
			if ( $old_logo ) {
				wp_delete_attachment( $old_logo, true );
			}
			delete_user_meta( $user_id, $logo_meta );
		} elseif ( ! empty( $_FILES['shop_logo']['name'] ) && empty( $_FILES['shop_logo']['error'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			$attachment_id = media_handle_upload( 'shop_logo', 0 );
			if ( is_wp_error( $attachment_id ) ) {
				wp_send_json_error( [ 'message' => $attachment_id->get_error_message() ], 422 );
			}

			if ( ! wp_attachment_is_image( $attachment_id ) ) {
				wp_delete_attachment( $attachment_id, true );
				wp_send_json_error( [ 'message' => __( 'Please upload a valid image file.', 'reseller-management' ) ], 422 );
			}

			wp_update_post(
				[
					'ID'          => $attachment_id,
					'post_author' => $user_id,
				]
			);

			$old_logo = (int) get_user_meta( $user_id, $logo_meta, true );
			if ( $old_logo && $old_logo !== (int) $attachment_id ) {
				wp_delete_attachment( $old_logo, true );
			}

			update_user_meta( $user_id, $logo_meta, (int) $attachment_id );
		}

		$logo_url = Reseller_Helper::get_shop_logo_url( $user_id, 'full' );

		wp_send_json_success(
			[
				'slug'       => $slug,
				'shopUrl'    => Reseller_Helper::get_shop_url( $user_id ),
				'brandColor' => $brand_color,
				'logoUrl'    => $logo_url,
				'message'    => __( 'Shop settings saved.', 'reseller-management' ),
			]
		);
	}

	/**
	 * AJAX: add to cart.
	 *
	 * @return void
	 */
	public function handle_add_to_cart() {
		check_ajax_referer( 'rm_shop_nonce', 'nonce' );

		$slug        = sanitize_title( wp_unslash( $_POST['shop_slug'] ?? '' ) );
		$reseller_id = $this->resolve_shop_reseller( $slug );
		if ( ! $reseller_id ) {
			wp_send_json_error( [ 'message' => __( 'Shop not found.', 'reseller-management' ) ], 404 );
		}

		$product_id = absint( $_POST['product_id'] ?? 0 );
		$quantity   = max( 1, absint( $_POST['quantity'] ?? 1 ) );
		$product    = wc_get_product( $product_id );

		if ( ! $product || 'publish' !== $product->get_status() ) {
			wp_send_json_error( [ 'message' => __( 'Invalid product.', 'reseller-management' ) ], 422 );
		}

		$parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		if ( ! Reseller_Helper::is_product_visible_in_shop( $reseller_id, $parent_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Product not available in this shop.', 'reseller-management' ) ], 422 );
		}

		$price = Reseller_Helper::get_reseller_selling_price( $reseller_id, $product );
		$cart  = $this->get_cart( $reseller_id );
		$found = false;

		foreach ( $cart as &$item ) {
			if ( (int) $item['product_id'] === $product_id ) {
				$item['quantity'] = absint( $item['quantity'] ) + $quantity;
				$item['price']    = $price;
				$found            = true;
				break;
			}
		}
		unset( $item );

		if ( ! $found ) {
			$cart[] = [
				'product_id' => $product_id,
				'name'       => $product->get_name(),
				'price'      => $price,
				'quantity'   => $quantity,
				'image'      => wp_get_attachment_image_url( (int) $product->get_image_id(), 'thumbnail' ),
			];
		}

		$this->set_cart( $reseller_id, $cart );

		wp_send_json_success(
			[
				'cartCount' => $this->get_cart_count( $cart ),
				'cartTotal' => $this->get_cart_total( $cart ),
				'message'   => __( 'Added to bag', 'reseller-management' ),
			]
		);
	}

	/**
	 * AJAX: update cart quantity.
	 *
	 * @return void
	 */
	public function handle_update_cart() {
		check_ajax_referer( 'rm_shop_nonce', 'nonce' );

		$slug        = sanitize_title( wp_unslash( $_POST['shop_slug'] ?? '' ) );
		$reseller_id = $this->resolve_shop_reseller( $slug );
		if ( ! $reseller_id ) {
			wp_send_json_error( [ 'message' => __( 'Shop not found.', 'reseller-management' ) ], 404 );
		}

		$product_id = absint( $_POST['product_id'] ?? 0 );
		$quantity   = max( 0, absint( $_POST['quantity'] ?? 0 ) );
		$cart       = $this->get_cart( $reseller_id );
		$new_cart   = [];

		foreach ( $cart as $item ) {
			if ( (int) $item['product_id'] === $product_id ) {
				if ( $quantity > 0 ) {
					$item['quantity'] = $quantity;
					$new_cart[]       = $item;
				}
				continue;
			}
			$new_cart[] = $item;
		}

		$this->set_cart( $reseller_id, $new_cart );

		wp_send_json_success(
			[
				'cartCount' => $this->get_cart_count( $new_cart ),
				'cartTotal' => $this->get_cart_total( $new_cart ),
				'items'     => $new_cart,
			]
		);
	}

	/**
	 * AJAX: remove cart item.
	 *
	 * @return void
	 */
	public function handle_remove_cart_item() {
		$_POST['quantity'] = 0;
		$this->handle_update_cart();
	}

	/**
	 * AJAX: place COD order from public shop.
	 *
	 * @return void
	 */
	public function handle_place_order() {
		check_ajax_referer( 'rm_shop_nonce', 'nonce' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_send_json_error( [ 'message' => __( 'WooCommerce is required.', 'reseller-management' ) ], 500 );
		}

		$slug        = sanitize_title( wp_unslash( $_POST['shop_slug'] ?? '' ) );
		$reseller_id = $this->resolve_shop_reseller( $slug );
		if ( ! $reseller_id ) {
			wp_send_json_error( [ 'message' => __( 'Shop not found.', 'reseller-management' ) ], 404 );
		}

		$customer_name    = sanitize_text_field( wp_unslash( $_POST['customer_name'] ?? '' ) );
		$customer_phone   = sanitize_text_field( wp_unslash( $_POST['customer_phone'] ?? '' ) );
		$customer_address = sanitize_textarea_field( wp_unslash( $_POST['customer_address'] ?? '' ) );
		$shipping_area    = sanitize_text_field( wp_unslash( $_POST['shipping_area'] ?? '' ) );
		$district         = sanitize_text_field( wp_unslash( $_POST['district'] ?? '' ) );
		$thana            = sanitize_text_field( wp_unslash( $_POST['thana'] ?? '' ) );
		$order_notes      = sanitize_textarea_field( wp_unslash( $_POST['order_notes'] ?? '' ) );
		$shipping_charge  = isset( $_POST['shipping_charge'] ) ? max( 0.0, round( (float) wp_unslash( $_POST['shipping_charge'] ), 2 ) ) : 0.0;

		if ( '' === $district && '' !== $shipping_area ) {
			$district = $shipping_area;
		}

		// Prefer configured preset charge when area title matches.
		$presets = Reseller_Helper::get_shipping_presets();
		if ( empty( $presets ) ) {
			$presets = [
				[ 'title' => 'Inside Dhaka', 'charge' => 60.0 ],
				[ 'title' => 'Outside Dhaka', 'charge' => 120.0 ],
			];
		}
		foreach ( $presets as $preset ) {
			if ( isset( $preset['title'] ) && $preset['title'] === $shipping_area && isset( $preset['charge'] ) ) {
				$shipping_charge = (float) $preset['charge'];
				break;
			}
		}

		$cart = $this->get_cart( $reseller_id );

		if ( '' === trim( $customer_name ) || '' === trim( $customer_phone ) || '' === trim( $customer_address ) || empty( $cart ) ) {
			wp_send_json_error( [ 'message' => __( 'Please complete all fields and add products.', 'reseller-management' ) ], 422 );
		}

		$name_parts = preg_split( '/\s+/', $customer_name );
		$first_name = $name_parts[0] ?? $customer_name;
		$last_name  = isset( $name_parts[1] ) ? implode( ' ', array_slice( $name_parts, 1 ) ) : '';

		$order = wc_create_order();
		if ( is_wp_error( $order ) ) {
			wp_send_json_error( [ 'message' => $order->get_error_message() ], 500 );
		}

		foreach ( $cart as $item ) {
			$product_id   = absint( $item['product_id'] ?? 0 );
			$quantity     = absint( $item['quantity'] ?? 1 );
			$product      = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}

			$parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
			if ( ! Reseller_Helper::is_product_visible_in_shop( $reseller_id, $parent_id ) ) {
				continue;
			}

			$resale_price = Reseller_Helper::get_reseller_selling_price( $reseller_id, $product );

			$order->add_product(
				$product,
				$quantity,
				[
					'subtotal' => $resale_price * $quantity,
					'total'    => $resale_price * $quantity,
				]
			);

			$order_items = $order->get_items();
			$last_item   = end( $order_items );
			if ( $last_item ) {
				$last_item->add_meta_data( '_resale_price', $resale_price );
				$last_item->add_meta_data( '_base_price', $product->get_price() );
				$last_item->save();
			}
		}

		if ( ! count( $order->get_items() ) ) {
			$order->delete( true );
			wp_send_json_error( [ 'message' => __( 'No valid products in cart.', 'reseller-management' ) ], 422 );
		}

		$address = [
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'phone'      => $customer_phone,
			'address_1'  => $customer_address,
			'address_2'  => $thana,
			'city'       => $district,
		];

		$order->set_address( $address, 'billing' );
		$order->set_address( $address, 'shipping' );

		if ( $order_notes ) {
			$order->set_customer_note( $order_notes );
		}

		if ( $shipping_charge > 0 ) {
			$shipping_item = new \WC_Order_Item_Shipping();
			$shipping_item->set_method_title( $shipping_area ? $shipping_area : __( 'Shipping', 'reseller-management' ) );
			$shipping_item->set_total( $shipping_charge );
			$order->add_item( $shipping_item );
			$order->update_meta_data( '_shipping_base_charge', $shipping_charge );
		}

		$order->update_meta_data( '_assigned_reseller_id', $reseller_id );
		$order->update_meta_data( '_order_district', $district );
		$order->update_meta_data( '_order_thana', $thana );
		if ( $shipping_area ) {
			$order->update_meta_data( '_order_shipping_area', $shipping_area );
		}
		$order->update_meta_data( '_rm_shop_order', 1 );
		$order->calculate_totals();
		$order->set_status( 'processing' );
		$order->save();

		$this->set_cart( $reseller_id, [] );

		$thank_you_url = add_query_arg(
			'order',
			$order->get_id(),
			trailingslashit( Reseller_Helper::get_shop_url( $reseller_id ) ) . 'thank-you/'
		);

		wp_send_json_success(
			[
				'order_id'     => $order->get_id(),
				'redirect'     => $thank_you_url,
				'thank_you_url'=> $thank_you_url,
				'message'      => sprintf(
					/* translators: %d: order id */
					__( 'Order #%d placed successfully.', 'reseller-management' ),
					$order->get_id()
				),
			]
		);
	}
}
